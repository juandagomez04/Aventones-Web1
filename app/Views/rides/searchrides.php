<?php
/* app/Views/rides/searchrides.php */
session_start();

require_once __DIR__ . '/../../Application/Services/Auth/login_user.php';
require_once __DIR__ . '/../../Application/Services/Rides/ManageRides.php';
require_once __DIR__ . '/../../Application/Services/Bookings/ManageBookings.php';

// Inicializar TODAS las variables primero
$isLoggedIn = false;
$role = '';
$userId = 0;
$origin = '';
$destination = '';
$sort = 'time';
$dir = 'asc';
$origins = [];
$map = [];
$destForSelected = [];
$rides = [];
$mapJson = '{}';

// ---- Verificar si el usuario está logueado ----
$isLoggedIn = LoginUser::isLoggedIn();
if ($isLoggedIn) {
    $role = $_SESSION['user_role'] ?? '';
    $userId = (int) ($_SESSION['user_id'] ?? 0);
}

// ---- Procesar logout (solo si está logueado) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $isLoggedIn) {
    LoginUser::logout();
    header('Location: ../auth/login.php');
    exit;
}

// ---- Procesar parámetros GET ----
$origin = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$sort = $_GET['sort'] ?? 'time';
$dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

// ---- Obtener datos para los combos ----
try {
    $origins = ManageRides::getDistinctOrigins();
    $map = ManageRides::getOriginDestinationMap();
    $destForSelected = $origin !== '' ? ManageRides::getDestinationsByOrigin($origin) : [];
    $mapJson = json_encode($map, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
} catch (Exception $e) {
    $origins = [];
    $map = [];
    $destForSelected = [];
    $mapJson = '{}';
}

// ---- Obtener y ordenar rides ----
try {
    $rides = ManageRides::search($origin ?: null, $destination ?: null, null);

    // Ordenar resultados (por defecto: fecha/hora más actual primero)
    usort($rides, function ($a, $b) use ($sort, $dir) {
        $mul = $dir === 'desc' ? -1 : 1;

        if ($sort === 'time') {
            // Ordenar por fecha/hora (más actual primero por defecto)
            $timeA = strtotime($a['time']);
            $timeB = strtotime($b['time']);
            return $mul * ($timeA - $timeB);
        } elseif ($sort === 'origin') {
            return $mul * strcmp((string) $a['origin'], (string) $b['origin']);
        } elseif ($sort === 'destination') {
            return $mul * strcmp((string) $a['destination'], (string) $b['destination']);
        }

        return 0;
    });
} catch (Exception $e) {
    $rides = [];
}

// ---- Función para obtener asientos disponibles ----
function getAvailableSeats($rideId)
{
    try {
        return ManageBookings::getAvailableSeats($rideId);
    } catch (Exception $e) {
        return 0;
    }
}

// ---- Función para verificar si ya tiene reserva ----
function hasActiveBooking($rideId, $userId)
{
    if ($userId === 0)
        return false;
    try {
        return ManageBookings::hasActiveBooking($rideId, $userId);
    } catch (Exception $e) {
        return false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Search Rides - AVENTONES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/home.css">

    <style>
        
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </div>

    <!-- Menú -->
    <div class="menu-container">
        <div class="menu">
            <nav class="left-menu">
                <a class="active" href="../rides/searchrides.php">Home</a>
                <?php if ($isLoggedIn): ?>
                    <?php if ($role === 'driver'): ?>
                        <a href="../myrides/myrides.php">My Rides</a>
                        <a href="../bookings/bookings.php">Bookings</a>
                    <?php elseif ($role === 'passenger'): ?>
                        <a href="../bookings/bookings.php">My Bookings</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <div class="center-search">
                <input type="text" placeholder="Search..." class="search-bar">
            </div>

            <div class="right-menu">
                <?php if ($isLoggedIn): ?>
                    <div class="user-btn">
                        <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
                        <div class="dropdown-menu">
                            <form method="POST">
                                <button type="submit" name="logout" value="true" class="logout-btn">Logout</button>
                            </form>
                            <a href="../profile/editprofile.php">Profile</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="../auth/login.php" class="btn btn-outline">Login</a>
                        <a href="../auth/register_passenger.php" class="btn btn-primary">Register</a>
                    </div>
                <?php endif; ?>
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
                <option value="time" <?= $sort === 'time' ? 'selected' : '' ?>>Fecha/Hora</option>
                <option value="origin" <?= $sort === 'origin' ? 'selected' : '' ?>>Origen</option>
                <option value="destination" <?= $sort === 'destination' ? 'selected' : '' ?>>Destino</option>
            </select>

            <select class="input" name="dir">
                <option value="asc" <?= $dir === 'asc' ? 'selected' : '' ?>>Ascendente</option>
                <option value="desc" <?= $dir === 'desc' ? 'selected' : '' ?>>Descendente</option>
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
                    <?php if (!empty($rides)): ?>
                        <?php foreach ($rides as $r):
                            $rideId = (int) $r['id'];
                            $veh = trim(($r['make'] ?? '') . ' ' . ($r['model'] ?? '') . ' ' . ($r['year'] ?? ''));
                            $avail = getAvailableSeats($rideId);
                            $already = ($role === 'passenger') ? hasActiveBooking($rideId, $userId) : false;
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
                                    <!-- Botón de ver detalles (disponible para todos) -->
                                    <a href="detailsride.php?id=<?= $rideId ?>" class="btn btn-sm btn-info">Ver Detalles</a>

                                    <?php if (!$isLoggedIn): ?>
                                        <!-- Usuario no logueado -->
                                        <a href="../auth/login.php" class="btn btn-sm btn-primary">Login para reservar</a>
                                    <?php elseif ($role === 'driver'): ?>
                                        <!-- Usuario driver logueado - NO puede reservar -->
                                        <span class="text-muted">Solo para pasajeros</span>
                                    <?php elseif ($role === 'passenger'): ?>
                                        <!-- Usuario pasajero logueado -->
                                        <?php if ($avail <= 0): ?>
                                            <span class="text-muted">Sin asientos</span>
                                        <?php elseif ($already): ?>
                                            <span class="text-muted">Ya solicitado</span>
                                        <?php else: ?>
                                            <form method="post" action="../bookings/bookings.php" class="inline">
                                                <input type="hidden" name="action" value="create">
                                                <input type="hidden" name="ride_id" value="<?= $rideId ?>">
                                                <button class="btn btn-sm btn-success">Solicitar reserva</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No se encontraron viajes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Footer público -->
    <footer class="container">
        <hr>
        <nav>
            <a href="../rides/searchrides.php">Home</a> |
            <?php if ($isLoggedIn): ?>
                <?php if ($role === 'driver'): ?>
                    <a href="../myrides/myrides.php">My Rides</a> |
                <?php elseif ($role === 'passenger'): ?>
                    <a href="../bookings/bookings.php">My Bookings</a> |
                <?php endif; ?>
                <a href="../profile/configuration.php">Settings</a> |
            <?php endif; ?>
            <a href="../auth/login.php">Login</a> |
            <a href="../auth/register_passenger.php">Register</a>
        </nav>
        <p>&copy; Aventones.com</p>
    </footer>

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