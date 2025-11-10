<?php
// app/Application/Services/Rides/ManageRides.php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/db_conexion.php'; // $pdo

final class ManageRides
{
    /** Crear ride */
    public static function createRide(int $driverId, array $data): int
    {
        global $pdo;
        self::validate($data);

        // Validar que el vehículo le pertenece al driver
        $own = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE id=? AND driver_id=?");
        $own->execute([(int) $data['vehicle_id'], $driverId]);
        if ((int) $own->fetchColumn() === 0) {
            throw new RuntimeException("Vehicle does not belong to this driver.");
        }

        $sql = "INSERT INTO rides (driver_id, vehicle_id, name, origin, destination, days, time, seat_price, seats_total)
                VALUES (:driver_id,:vehicle_id,:name,:origin,:destination,:days,:time,:seat_price,:seats_total)";
        $st = $pdo->prepare($sql);
        $st->execute([
            ':driver_id' => $driverId,
            ':vehicle_id' => (int) $data['vehicle_id'],
            ':name' => trim($data['name']),
            ':origin' => trim($data['origin']),
            ':destination' => trim($data['destination']),
            ':days' => self::daysToString($data['days'] ?? []),
            ':time' => self::normalizeTime($data['time']),
            ':seat_price' => (string) self::money($data['seat_price']),
            ':seats_total' => (int) $data['seats_total'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** Actualizar ride (solo del driver) */
    public static function updateRide(int $driverId, int $rideId, array $data): bool
    {
        global $pdo;
        self::validate($data, true);

        $ok = self::isOwner($driverId, $rideId);
        if (!$ok)
            throw new RuntimeException("You cannot edit this ride.");

        // si se cambia vehículo, verificar pertenencia
        if (!empty($data['vehicle_id'])) {
            $own = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE id=? AND driver_id=?");
            $own->execute([(int) $data['vehicle_id'], $driverId]);
            if ((int) $own->fetchColumn() === 0) {
                throw new RuntimeException("Vehicle does not belong to this driver.");
            }
        }

        $sql = "UPDATE rides SET vehicle_id=:vehicle_id, name=:name, origin=:origin, destination=:destination,
                days=:days, time=:time, seat_price=:seat_price, seats_total=:seats_total
                WHERE id=:id AND driver_id=:driver_id";
        $st = $pdo->prepare($sql);
        return $st->execute([
            ':vehicle_id' => (int) $data['vehicle_id'],
            ':name' => trim($data['name']),
            ':origin' => trim($data['origin']),
            ':destination' => trim($data['destination']),
            ':days' => self::daysToString($data['days'] ?? []),
            ':time' => self::normalizeTime($data['time']),
            ':seat_price' => (string) self::money($data['seat_price']),
            ':seats_total' => (int) $data['seats_total'],
            ':id' => $rideId,
            ':driver_id' => $driverId,
        ]);
    }

    /** Eliminar (solo del driver) */
    public static function deleteRide(int $driverId, int $rideId): bool
    {
        global $pdo;
        if (!self::isOwner($driverId, $rideId))
            return false;
        $st = $pdo->prepare("DELETE FROM rides WHERE id=:id AND driver_id=:driver_id");
        return $st->execute([':id' => $rideId, ':driver_id' => $driverId]);
    }

    /** Obtener un ride */
    public static function getRide(int $rideId): ?array
    {
        global $pdo;
        $st = $pdo->prepare("SELECT r.*, v.plate, v.make, v.model, v.color, v.photo_path FROM rides r
                         LEFT JOIN vehicles v ON v.id=r.vehicle_id WHERE r.id=?");
        $st->execute([$rideId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Listar por driver */
    public static function listByDriver(int $driverId): array
    {
        global $pdo;
        $st = $pdo->prepare("SELECT r.*, v.plate, v.make, v.model, v.photo_path FROM rides r
                                    LEFT JOIN vehicles v ON v.id=r.vehicle_id
                                    
                                    WHERE r.driver_id=? ORDER BY r.id DESC");
        $st->execute([$driverId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Búsqueda para pasajeros */
    public static function search(?string $origin, ?string $destination, ?string $day): array
    {
        global $pdo;
        $w = [];
        $p = [];
        if ($origin) {
            $w[] = "r.origin LIKE ?";
            $p[] = '%' . $origin . '%';
        }
        if ($destination) {
            $w[] = "r.destination LIKE ?";
            $p[] = '%' . $destination . '%';
        }
        if ($day) {
            $w[] = "r.days LIKE ?";
            $p[] = '%' . $day . '%';
        }
        $where = $w ? ('WHERE ' . implode(' AND ', $w)) : '';
        $sql = "SELECT r.*, v.plate, v.make, v.model, u.first_name, u.last_name
                FROM rides r
                JOIN users u ON u.id=r.driver_id
                LEFT JOIN vehicles v ON v.id=r.vehicle_id
                $where
                ORDER BY r.id DESC LIMIT 100";
        $st = $pdo->prepare($sql);
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------- Helpers ---------- */

    private static function isOwner(int $driverId, int $rideId): bool
    {
        global $pdo;
        $st = $pdo->prepare("SELECT COUNT(*) FROM rides WHERE id=? AND driver_id=?");
        $st->execute([$rideId, $driverId]);
        return (int) $st->fetchColumn() === 1;
    }

    private static function daysToString(array $days): string
    {
        // Espera array como ['Mon','Tue',...]
        $days = array_values(array_intersect($days, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']));
        return implode(',', $days);
    }

    private static function normalizeTime(string $t): string
    {
        // input type="time" -> HH:MM[:SS]
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $t))
            throw new RuntimeException("Invalid time.");
        return strlen($t) === 5 ? ($t . ':00') : $t;
    }

    private static function money($v): float
    {
        if (!is_numeric($v))
            throw new RuntimeException("Invalid price.");
        $v = (float) $v;
        if ($v < 0)
            throw new RuntimeException("Price must be >= 0.");
        return round($v, 2);
    }

    private static function validate(array $d, bool $update = false): void
    {
        $need = ['vehicle_id', 'name', 'origin', 'destination', 'time', 'seat_price', 'seats_total'];
        foreach ($need as $k)
            if (empty($d[$k]))
                throw new RuntimeException("Missing field: $k");
        if ((int) $d['seats_total'] <= 0)
            throw new RuntimeException("Seats must be > 0.");
        if (strlen(trim($d['name'])) > 80)
            throw new RuntimeException("Name too long.");
    }

    /** Obtiene todos los orígenes únicos */
    public static function getDistinctOrigins(): array {
        global $pdo;
        $sql = "SELECT DISTINCT origin FROM rides ORDER BY origin ASC";
        $st = $pdo->query($sql);
        return array_map(fn($r) => $r['origin'], $st->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Obtiene todos los destinos para un origen específico */
    public static function getDestinationsByOrigin(string $origin): array {
        global $pdo;
        $sql = "SELECT DISTINCT destination FROM rides WHERE origin = ? ORDER BY destination ASC";
        $st = $pdo->prepare($sql);
        $st->execute([$origin]);
        return array_map(fn($r) => $r['destination'], $st->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Retorna un mapa origen => [destinos...] para cargar en JS */
    public static function getOriginDestinationMap(): array {
        global $pdo;
        $sql = "SELECT origin, destination FROM rides GROUP BY origin, destination ORDER BY origin ASC, destination ASC";
        $st = $pdo->query($sql);
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $o = $row['origin'];
            $d = $row['destination'];
            if (!isset($map[$o])) $map[$o] = [];
            $map[$o][] = $d;
        }
        return $map;
    }
}
