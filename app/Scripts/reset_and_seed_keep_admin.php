<?php
// app/Scripts/reset_and_seed_keep_admin.php
declare(strict_types=1);

// ====== Paths base (relativos al script) ======
$baseDir = realpath(__DIR__ . '/../../'); // .../Proyecto 1
$defaultUserImg = $baseDir . '/public/assets/img/avatar.png';
$defaultVehImg = $baseDir . '/public/assets/img/Icono.png';
$userDir = $baseDir . '/public/assets/img/users/';
$vehDir = $baseDir . '/public/assets/img/vehicles/';

@is_dir($userDir) || mkdir($userDir, 0777, true);
@is_dir($vehDir) || mkdir($vehDir, 0777, true);

// ====== DB ======
$DB_HOST = '127.0.0.1';
$DB_NAME = 'aventones';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";

function q(PDO $pdo, string $sql, array $p = []): PDOStatement
{
    $st = $pdo->prepare($sql);
    $st->execute($p);
    return $st;
}

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->beginTransaction();

    // 1) Preservar el admin con menor id
    $adminId = q($pdo, "SELECT MIN(id) FROM users WHERE role='admin'")->fetchColumn();
    if (!$adminId) {
        throw new RuntimeException("No existe usuario admin para preservar.");
    }
    echo "Admin preservado (id={$adminId})\n";

    // 2) Desactivar FK y limpiar
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("DELETE FROM bookings");
    q($pdo, "DELETE FROM rides WHERE driver_id <> :a", [':a' => $adminId]);
    q($pdo, "DELETE FROM vehicles WHERE driver_id <> :a", [':a' => $adminId]);
    try {
        q($pdo, "DELETE FROM activation_tokens WHERE user_id <> :a", [':a' => $adminId]);
    } catch (\Throwable $e) {
    }
    q($pdo, "DELETE FROM users WHERE id <> :a", [':a' => $adminId]);
    foreach (['users', 'vehicles', 'rides', 'bookings'] as $t) {
        try {
            $pdo->exec("ALTER TABLE {$t} AUTO_INCREMENT=1");
        } catch (\Throwable $e) {
        }
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    // 3) Asegurar admin activo
    q($pdo, "UPDATE users SET status='active' WHERE id=:a", [':a' => $adminId]);

    // 4) Insertar usuarios (drivers + passengers) con foto
    $insUser = $pdo->prepare("
        INSERT INTO users (role, status, first_name, last_name, national_id, birth_date, email, phone, photo_path, password_hash)
        VALUES (:role, :status, :fn, :ln, :nid, :bd, :email, :phone, :photo, :ph)
    ");

    $seedUsers = [
        ['driver', 'active', 'María', 'Rojas', '1-1111-1111', '1992-06-10', 'driver1@aventones.local', '7000-0001'],
        ['driver', 'active', 'Carlos', 'Méndez', '2-2222-2222', '1989-03-21', 'driver2@aventones.local', '7000-0002'],
        ['passenger', 'active', 'Lucía', 'Vega', '3-3333-3333', '2000-11-01', 'pax1@aventones.local', '7100-0001'],
        ['passenger', 'active', 'Javier', 'Solano', '4-4444-4444', '1998-01-15', 'pax2@aventones.local', '7100-0002'],
        ['passenger', 'active', 'Ana', 'Hernández', '5-5555-5555', '1999-09-09', 'pax3@aventones.local', '7100-0003'],
    ];

    $passwordHash = password_hash('123456', PASSWORD_BCRYPT);

    foreach ($seedUsers as $u) {
        $fileName = uniqid('user_') . '.png';
        $relPath = "public/assets/img/users/{$fileName}";
        if (!@copy($defaultUserImg, $userDir . $fileName)) {
            throw new RuntimeException("No se pudo copiar avatar base a {$userDir}{$fileName}");
        }
        $insUser->execute([
            ':role' => $u[0],
            ':status' => $u[1],
            ':fn' => $u[2],
            ':ln' => $u[3],
            ':nid' => $u[4],
            ':bd' => $u[5],
            ':email' => $u[6],
            ':phone' => $u[7],
            ':photo' => $relPath,
            ':ph' => $passwordHash
        ]);
    }

    $driverIds = q($pdo, "SELECT id FROM users WHERE role='driver' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $passengerIds = q($pdo, "SELECT id FROM users WHERE role='passenger' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

    // 5) Insertar vehículos (uno por driver)
    $insVeh = $pdo->prepare("
        INSERT INTO vehicles (driver_id, plate, color, make, model, year, seats_capacity, photo_path)
        VALUES (:driver_id, :plate, :color, :make, :model, :year, :seats, :photo)
    ");
    $vehSeed = [
        [$driverIds[0] ?? null, 'ABC-123', 'Rojo', 'Toyota', 'Corolla', 2018, 4],
        [$driverIds[1] ?? null, 'XYZ-456', 'Azul', 'Hyundai', 'Accent', 2020, 4],
    ];
    foreach ($vehSeed as $v) {
        if ($v[0] === null) {
            continue;
        }
        $fileName = uniqid('veh_') . '.png';
        $relPath = "public/assets/img/vehicles/{$fileName}";
        @copy($defaultVehImg, $vehDir . $fileName);
        $insVeh->execute([
            ':driver_id' => $v[0],
            ':plate' => $v[1],
            ':color' => $v[2],
            ':make' => $v[3],
            ':model' => $v[4],
            ':year' => $v[5],
            ':seats' => $v[6],
            ':photo' => $relPath
        ]);
    }

    // 6) Mapa driver_id → vehicle_id (clave correcta)
    $vehForDriver = q($pdo, "SELECT driver_id, id FROM vehicles")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 7) Rides
    $insRide = $pdo->prepare("
        INSERT INTO rides (driver_id, vehicle_id, name, origin, destination, days, time, seat_price, seats_total)
        VALUES (:driver_id, :vehicle_id, :name, :origin, :destination, :days, :time, :price, :seats_total)
    ");

    $rides = [
        [$driverIds[0] ?? 0, 'Alajuela → San José', 'Alajuela', 'San José', 'Mon,Wed,Fri', '08:30:00', 2500.00, 3],
        [$driverIds[0] ?? 0, 'Heredia → Cartago', 'Heredia', 'Cartago', 'Tue,Thu', '14:00:00', 3000.00, 2],
        [$driverIds[1] ?? 0, 'San José → Puntarenas', 'San José', 'Puntarenas', 'Sat', '07:30:00', 6000.00, 4],
        [$driverIds[1] ?? 0, 'Cartago → San José', 'Cartago', 'San José', 'Daily', '17:15:00', 2500.00, 3],
    ];

    foreach ($rides as $r) {
        [$dId, $name, $orig, $dest, $days, $time, $price, $seats] = $r;
        if (!$dId || !isset($vehForDriver[$dId]))
            continue;
        $insRide->execute([
            ':driver_id' => $dId,
            ':vehicle_id' => $vehForDriver[$dId],
            ':name' => $name,
            ':origin' => $orig,
            ':destination' => $dest,
            ':days' => $days,
            ':time' => $time,
            ':price' => $price,
            ':seats_total' => $seats
        ]);
    }

    $rideIds = q($pdo, "SELECT id FROM rides ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $passengerIds = q($pdo, "SELECT id FROM users WHERE role='passenger' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

    // 8) Bookings
    $insBk = $pdo->prepare("
        INSERT INTO bookings (ride_id, passenger_id, status, created_at)
        VALUES (:ride_id, :passenger_id, :status, :created_at)
    ");
    $now = new DateTimeImmutable();
    $older = $now->modify('-3 minutes')->format('Y-m-d H:i:s');
    $just = $now->format('Y-m-d H:i:s');

    $seedBk = [
        [$rideIds[0] ?? null, $passengerIds[0] ?? null, 'pending', $older],
        [$rideIds[0] ?? null, $passengerIds[1] ?? null, 'accepted', $just],
        [$rideIds[1] ?? null, $passengerIds[2] ?? null, 'rejected', $just],
        [$rideIds[2] ?? null, $passengerIds[0] ?? null, 'pending', $just],
        [$rideIds[3] ?? null, $passengerIds[1] ?? null, 'pending', $older],
        [$rideIds[3] ?? null, $passengerIds[2] ?? null, 'cancelled', $just],
    ];
    foreach ($seedBk as $b) {
        if ($b[0] && $b[1]) {
            $insBk->execute([
                ':ride_id' => $b[0],
                ':passenger_id' => $b[1],
                ':status' => $b[2],
                ':created_at' => $b[3]
            ]);
        }
    }

    $pdo->commit();

    echo "\n✅ Reset & seed completado.\n";
    echo "   Contraseña de todos los usuarios: 123456\n";
    echo "   Admin preservado (id={$adminId})\n";

} catch (\Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (\Throwable $ignored) {
        }
    }
    fwrite(STDERR, "❌ Error: {$e->getMessage()}\n");
    exit(1);
}

