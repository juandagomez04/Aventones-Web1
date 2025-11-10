<?php
/* app/Views/rides/searchrides.php */
session_start();

require_once __DIR__ . '/../../Application/Services/Auth/login_user.php';
require_once __DIR__ . '/../../Application/Services/Rides/ManageRides.php';
require_once __DIR__ . '/../../Application/Services/Bookings/ManageBookings.php';



if (!LoginUser::isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// ---- Logout ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    LoginUser::logout();
    header('Location: ../auth/login.php');
    exit;
}

$role = $_SESSION['user_role'] ?? '';
$userId = (int) ($_SESSION['user_id'] ?? 0);

/* ==== Filtros / Orden ==== */
$origin = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$sort = $_GET['sort'] ?? 'time';
$dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

/* ==== Combos dependientes ==== */
$origins = ManageRides::getDistinctOrigins();           // NUEVO en ManageRides
$map = ManageRides::getOriginDestinationMap();      // NUEVO en ManageRides
$destForSelected = $origin !== '' ? ManageRides::getDestinationsByOrigin($origin) : [];
$mapJson = json_encode($map, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

/* ==== Datos ==== */
$rides = ManageRides::search($origin ?: null, $destination ?: null, null);

/* Orden simple en PHP */
usort($rides, function ($a, $b) use ($sort, $dir) {
    $mul = $dir === 'desc' ? -1 : 1;
    $ka = $sort === 'origin' ? $a['origin'] : ($sort === 'destination' ? $a['destination'] : $a['time']);
    $kb = $sort === 'origin' ? $b['origin'] : ($sort === 'destination' ? $b['destination'] : $b['time']);
    return $mul * strcmp((string) $ka, (string) $kb);
});
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Search Rides - AVENTONES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/rides.css">
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </div>

    <!-- Menú EXACTO como indicaste -->
    <div class="menu-container">
        <div class="menu">
            <nav class="left-menu">
                <a class="active" href="../rides/searchrides.php">Home</a>
                <a href="../myrides/myrides.php">Rides</a>
                <a href="../bookings/bookings.php">Bookings</a>
            </nav>

            <div class="center-search">
                <input type="text" placeholder="Search..." class="search-bar">
            </div>

            <div class="right-menu">
                <div class="user-btn">
                    <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
                    <div class="dropdown-menu">
                        <form method="POST">
                            <button type="submit" name="logout" value="true" class="logout-btn">Logout</button>
                        </form>
                        <a href="../profile/editprofile.php">Profile</a>
                        <a href="../profile/configuration.php">Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        <!-- Filtros: COMBOS DEPENDIENTES -->
        <form class="filter-bar" method="get" id="searchForm">
            <select class="input" id="origin" name="origin" aria-label="Origen">
                <option value="">-- Origen --</option>
                <?php foreach ($origins as $o): ?>
                    <option value="<?= htmlspecialchars($o) ?>" <?= $o === $origin ? 'selected' : '' ?>>
                        <?= htmlspecialchars($o) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="input" id="destination" name="destination" aria-label="Destino" <?= $origin === '' ? 'disabled' : '' ?>>
                <option value="">-- Destino --</option>
                <?php foreach ($destForSelected as $d): ?>
                    <option value="<?= htmlspecialchars($d) ?>" <?= $d === $destination ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="input" name="sort">
                <option value="time" <?= $sort === 'time' ? 'selected' : '' ?>>Hora</option>
                <option value="origin" <?= $sort === 'origin' ? 'selected' : '' ?>>Origen</option>
                <option value="destination" <?= $sort === 'destination' ? 'selected' : '' ?>>Destino</option>
            </select>

            <select class="input" name="dir">
                <option value="asc" <?= $dir === 'asc' ? 'selected' : '' ?>>Asc</option>
                <option value="desc" <?= $dir === 'desc' ? 'selected' : '' ?>>Desc</option>
            </select>

            <button class="btn btn-primary">Buscar</button>
        </form>

        <!-- Tabla de rides -->
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Días</th>
                        <th>Hora</th>
                        <th>Vehículo</th>
                        <th>Asientos</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rides as $r):
                        $rideId = (int) $r['id'];
                        $veh = trim(($r['make'] ?? '') . ' ' . ($r['model'] ?? '') . ' ' . ($r['year'] ?? ''));
                        $avail = ManageBookings::getAvailableSeats($rideId);
                        $already = ($role === 'passenger') ? ManageBookings::hasActiveBooking($rideId, $userId) : false;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['origin']) ?></td>
                            <td><?= htmlspecialchars($r['destination']) ?></td>
                            <td><?= htmlspecialchars($r['days']) ?></td>
                            <td><?= htmlspecialchars(substr((string) $r['time'], 0, 5)) ?></td>
                            <td><?= htmlspecialchars($veh) ?></td>
                            <td><?= (int) $avail ?> / <?= (int) $r['seats_total'] ?></td>
                            <td class="text-end">
                                <!-- Botón de ver detalles (disponible para todos los roles) -->
                                <a href="detailsride.php?id=<?= $rideId ?>" class="btn btn-sm btn-info">Ver Detalles</a>

                                <?php if ($role !== 'passenger'): ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Solo pasajeros</button>
                                <?php else: ?>
                                    <?php if ($avail <= 0): ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Sin asientos</button>
                                    <?php elseif ($already): ?>
                                        <button class="btn btn-sm btn-outline" disabled>Ya solicitado</button>
                                    <?php else: ?>
                                        <form method="post" action="../bookings/bookings.php" class="inline"
                                            style="display:inline;">
                                            <input type="hidden" name="action" value="create">
                                            <input type="hidden" name="ride_id" value="<?= $rideId ?>">
                                            <button class="btn btn-sm btn-success">Solicitar reserva</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rides)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No se encontraron viajes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Combos dependientes -->
    <script>
        const ORIGIN_MAP = <?= $mapJson ?>;
        const originSel = document.getElementById('origin');
        const destSel = document.getElementById('destination');

        function fillDestinations(originValue, preselectValue = '') {
            destSel.innerHTML = '';
            const optEmpty = document.createElement('option');
            optEmpty.value = '';
            optEmpty.textContent = '-- Destino --';
            destSel.appendChild(optEmpty);

            const list = ORIGIN_MAP[originValue] || [];
            for (const d of list) {
                const opt = document.createElement('option');
                opt.value = d;
                opt.textContent = d;
                if (d === preselectValue) opt.selected = true;
                destSel.appendChild(opt);
            }
            destSel.disabled = (originValue === '' || list.length === 0);
        }

        originSel.addEventListener('change', () => fillDestinations(originSel.value, ''));

        <?php if ($origin !== ''): ?>
            fillDestinations(<?= json_encode($origin) ?>, <?= json_encode($destination) ?>);
        <?php endif; ?>
    </script>
</body>

</html>