<?php
// /Proyecto/app/Application/Services/Rides/ManageVehicles.php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/db_conexion.php';

final class ManageVehicles
{
    /**
     * Obtener todos los vehículos de un conductor
     */
    public static function getVehiclesByDriver(int $driverId): array
    {
        global $pdo;
        $sql = "SELECT * FROM vehicles WHERE driver_id = ? ORDER BY year DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$driverId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un vehículo específico
     */
    public static function getVehicleById(int $vehicleId, int $driverId): ?array
    {
        global $pdo;
        $sql = "SELECT * FROM vehicles WHERE id = ? AND driver_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vehicleId, $driverId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crear nuevo vehículo
     */
    public static function createVehicle(array $data, array $files = []): int
    {
        global $pdo;

        // Validaciones
        self::validateVehicleData($data);

        // Subir foto si existe
        $photoPath = null;
        if (!empty($files['photo']['tmp_name'])) {
            $photoPath = self::uploadVehiclePhoto($files['photo']);
        }

        $sql = "INSERT INTO vehicles 
                (driver_id, plate, color, make, model, year, seats_capacity, photo_path) 
                VALUES 
                (:driver_id, :plate, :color, :make, :model, :year, :seats_capacity, :photo_path)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':driver_id' => (int) $data['driver_id'],
            ':plate' => strtoupper(trim($data['plate'])),
            ':color' => trim($data['color']),
            ':make' => trim($data['make']),
            ':model' => trim($data['model']),
            ':year' => (int) $data['year'],
            ':seats_capacity' => (int) $data['seats_capacity'],
            ':photo_path' => $photoPath
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualizar vehículo
     */
    public static function updateVehicle(int $vehicleId, array $data, array $files = []): bool
    {
        global $pdo;

        // Validaciones
        self::validateVehicleData($data);

        // Obtener vehículo actual para mantener foto si no se sube nueva
        $currentVehicle = self::getVehicleById($vehicleId, (int) $data['driver_id']);
        if (!$currentVehicle) {
            throw new RuntimeException("Vehículo no encontrado.");
        }

        $photoPath = $currentVehicle['photo_path'];

        // Subir nueva foto si existe
        if (!empty($files['photo']['tmp_name'])) {
            // Eliminar foto anterior si existe
            if ($photoPath) {
                $oldPhotoPath = __DIR__ . '/../../../../public/' . $photoPath;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }
            $photoPath = self::uploadVehiclePhoto($files['photo']);
        }

        $sql = "UPDATE vehicles SET 
                plate = :plate,
                color = :color,
                make = :make,
                model = :model,
                year = :year,
                seats_capacity = :seats_capacity,
                photo_path = :photo_path
                WHERE id = :id AND driver_id = :driver_id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':plate' => strtoupper(trim($data['plate'])),
            ':color' => trim($data['color']),
            ':make' => trim($data['make']),
            ':model' => trim($data['model']),
            ':year' => (int) $data['year'],
            ':seats_capacity' => (int) $data['seats_capacity'],
            ':photo_path' => $photoPath,
            ':id' => $vehicleId,
            ':driver_id' => (int) $data['driver_id']
        ]);
    }

    /**
     * Eliminar vehículo
     */
    public static function deleteVehicle(int $vehicleId, int $driverId): bool
    {
        global $pdo;

        // Obtener vehículo para eliminar foto
        $vehicle = self::getVehicleById($vehicleId, $driverId);
        if ($vehicle && $vehicle['photo_path']) {
            $photoPath = __DIR__ . '/../../../../public/' . $vehicle['photo_path'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        $sql = "DELETE FROM vehicles WHERE id = ? AND driver_id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$vehicleId, $driverId]);
    }

    /**
     * Validar datos del vehículo
     */
    private static function validateVehicleData(array $data): void
    {
        $required = ['plate', 'color', 'make', 'model', 'year', 'seats_capacity'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new RuntimeException("El campo " . self::getFieldName($field) . " es requerido.");
            }
        }

        // Validar año (entre 1990 y año actual + 1)
        $currentYear = (int) date('Y');
        $year = (int) $data['year'];
        if ($year < 1990 || $year > $currentYear + 1) {
            throw new RuntimeException("El año debe estar entre 1990 y " . ($currentYear + 1));
        }

        // Validar capacidad de asientos
        $seatCapacity = (int) $data['seats_capacity'];
        if ($seatCapacity < 1 || $seatCapacity > 20) {
            throw new RuntimeException("La capacidad de asientos debe estar entre 1 y 20.");
        }

        // Validar placa única (excepto para el mismo vehículo en edición)
        $vehicleId = $data['vehicle_id'] ?? null;
        self::validateUniquePlate($data['plate'], (int) $data['driver_id'], $vehicleId);
    }

    /**
     * Validar que la placa sea única por conductor
     */
    private static function validateUniquePlate(string $plate, int $driverId, ?int $vehicleId = null): void
    {
        global $pdo;

        $sql = "SELECT id FROM vehicles WHERE plate = ? AND driver_id = ?";
        $params = [strtoupper($plate), $driverId];

        if ($vehicleId) {
            $sql .= " AND id != ?";
            $params[] = $vehicleId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetch()) {
            throw new RuntimeException("Ya existe un vehículo con esta placa.");
        }
    }

    /**
     * Subir foto del vehículo
     */
    private static function uploadVehiclePhoto(array $photoFile): string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (($photoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Error al subir el archivo: código " . ($photoFile['error'] ?? 'desconocido'));
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $photoFile['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            throw new RuntimeException("Solo se permiten imágenes JPEG, PNG, GIF o WebP.");
        }

        if (($photoFile['size'] ?? 0) > $maxSize) {
            throw new RuntimeException("La imagen no debe superar los 5MB.");
        }

        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg'
        };

        // Generar nombre de archivo similar al formato de usuarios
        $filename = 'v_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

        // Ruta absoluta en disco
        $absDir = __DIR__ . '/../../../../public/assets/img/vehicles/';

        if (!is_dir($absDir)) {
            @mkdir($absDir, 0755, true);
        }

        $absPath = $absDir . $filename;

        if (!move_uploaded_file($photoFile['tmp_name'], $absPath)) {
            throw new RuntimeException("Error al mover el archivo subido.");
        }
        @chmod($absPath, 0644);

        // Ruta para guardar en BD
        return 'assets/img/vehicles/' . $filename;
    }

    /**
     * Obtener nombre legible del campo
     */
    private static function getFieldName(string $field): string
    {
        $names = [
            'plate' => 'placa',
            'make' => 'marca',
            'model' => 'modelo',
            'year' => 'año',
            'seats_capacity' => 'capacidad de asientos',
            'color' => 'color'
        ];
        return $names[$field] ?? $field;
    }
}