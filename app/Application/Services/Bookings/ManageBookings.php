<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/db_conexion.php'; // $pdo (PDO)

final class ManageBookings
{
    /** Crear reserva (solo pasajeros) */
    public static function createBooking(int $rideId, int $passengerId): bool
    {
        global $pdo;

        // Ride existe
        $ride = self::getRideById($rideId);
        if (!$ride) {
            throw new RuntimeException("El viaje no existe.");
        }

        // No reservar propio ride
        if ((int) $ride['driver_id'] === $passengerId) {
            throw new RuntimeException("No puedes reservar tu propio viaje.");
        }

        // Duplicados (pending/accepted)
        if (self::hasActiveBooking($rideId, $passengerId)) {
            throw new RuntimeException("Ya tienes una reserva activa en este viaje.");
        }

        // Disponibilidad
        if (self::getAvailableSeats($rideId) <= 0) {
            throw new RuntimeException("No hay asientos disponibles.");
        }

        $st = $pdo->prepare("INSERT INTO bookings (ride_id, passenger_id, status) VALUES (?,?, 'pending')");
        return $st->execute([$rideId, $passengerId]);
    }

    /** Obtener ride */
    public static function getRideById(int $rideId): ?array
    {
        global $pdo;
        $st = $pdo->prepare("SELECT * FROM rides WHERE id=?");
        $st->execute([$rideId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** ¿Pasajero ya tiene reserva activa (pending/accepted) en el ride? */
    public static function hasActiveBooking(int $rideId, int $passengerId): bool
    {
        global $pdo;
        $st = $pdo->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE ride_id=? AND passenger_id=? AND status IN ('pending','accepted')
        ");
        $st->execute([$rideId, $passengerId]);
        return (int) $st->fetchColumn() > 0;
    }

    /** Asientos disponibles = total - aceptadas */
    public static function getAvailableSeats(int $rideId): int
    {
        global $pdo;

        $st = $pdo->prepare("SELECT seats_total FROM rides WHERE id=?");
        $st->execute([$rideId]);
        $total = (int) ($st->fetchColumn() ?? 0);

        $st = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE ride_id=? AND status='accepted'");
        $st->execute([$rideId]);
        $accepted = (int) $st->fetchColumn();

        return max(0, $total - $accepted);
    }

    /** Reservas del pasajero */
    public static function getBookingsByPassenger(int $passengerId): array
    {
        global $pdo;
        $sql = "SELECT b.*, r.name, r.origin, r.destination, r.time, r.days, r.seats_total,
                       v.make, v.model, v.year
                FROM bookings b
                JOIN rides r ON r.id = b.ride_id
                LEFT JOIN vehicles v ON v.id = r.vehicle_id
                WHERE b.passenger_id = ?
                ORDER BY b.id DESC";
        $st = $pdo->prepare($sql);
        $st->execute([$passengerId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Reservas sobre rides del driver */
    public static function getBookingsByDriver(int $driverId): array
    {
        global $pdo;
        $sql = "SELECT b.*, r.name, r.origin, r.destination, r.time, r.days, r.seats_total,
                       u.first_name, u.last_name,
                       v.make, v.model, v.year
                FROM bookings b
                JOIN rides r ON r.id = b.ride_id
                JOIN users u ON u.id = b.passenger_id
                LEFT JOIN vehicles v ON v.id = r.vehicle_id
                WHERE r.driver_id = ?
                ORDER BY b.id DESC";
        $st = $pdo->prepare($sql);
        $st->execute([$driverId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Info compacta del booking para validaciones */
    private static function getBookingContext(int $bookingId): ?array
    {
        global $pdo;
        $sql = "SELECT b.id, b.status, b.ride_id, b.passenger_id, r.driver_id
            FROM bookings b
            JOIN rides r ON r.id = b.ride_id
            WHERE b.id = ?";
        $st = $pdo->prepare($sql);
        $st->execute([$bookingId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Actualiza el estado de una reserva respetando reglas por rol y estado actual.
     * - Driver: PENDING -> ACCEPTED | REJECTED
     * - Passenger: PENDING|ACCEPTED -> CANCELED (solo su propia reserva)
     */
    public static function updateBookingStatus(int $bookingId, string $status, int $userId, string $role): bool
    {
        global $pdo;

        $status = strtolower($status);
        $allowed = ['accepted', 'rejected', 'cancelled'];

        if (!in_array($status, $allowed, true)) {
            throw new RuntimeException("Estado no válido.");
        }

        $ctx = self::getBookingContext($bookingId);
        if (!$ctx) {
            throw new RuntimeException("Reserva no encontrada.");
        }

        $current = strtolower((string) $ctx['status']);
        $rideId = (int) $ctx['ride_id'];
        $driverIdOfRide = (int) $ctx['driver_id'];
        $passengerIdOfBook = (int) $ctx['passenger_id'];

        // Idempotencia: si ya está en el estado solicitado, no hacer nada.
        if ($current === $status) {
            return true;
        }

        if ($role === 'driver') {
            // Validar propiedad: el driver que actúa debe ser dueño del ride
            if ($driverIdOfRide !== $userId) {
                throw new RuntimeException("No puedes modificar esta reserva.");
            }

            // Reglas de transición para driver:
            // Solo desde PENDING -> ACCEPTED o REJECTED
            if ($current !== 'pending') {
                throw new RuntimeException("Solo puedes gestionar reservas en estado PENDING.");
            }
            if (!in_array($status, ['accepted', 'rejected'], true)) {
                throw new RuntimeException("Acción no permitida para el driver.");
            }

            // Si va a aceptar, validar cupo disponible
            if ($status === 'accepted' && self::getAvailableSeats($rideId) <= 0) {
                throw new RuntimeException("No hay asientos disponibles para aceptar.");
            }

        } elseif ($role === 'passenger') {
            // Validar propiedad: el pasajero que actúa debe ser dueño del booking
            if ($passengerIdOfBook !== $userId) {
                throw new RuntimeException("No puedes cancelar esta reserva.");
            }

            // Reglas de transición para passenger:
            // Solo puede poner CANCELED si está PENDING o ACCEPTED
            if ($status !== 'cancelled') {
                throw new RuntimeException("Acción no permitida para el pasajero.");
            }

            if (!in_array($current, ['pending', 'accepted'], true)) {
                throw new RuntimeException("Solo puedes cancelar reservas PENDING o ACCEPTED.");
            }

        } else {
            throw new RuntimeException("Rol no permitido.");
        }

        $st = $pdo->prepare("UPDATE bookings SET status=? WHERE id=?");
        return $st->execute([$status, $bookingId]);
    }

}
