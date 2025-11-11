<?php
/* app/Views/rides/detailsride.php */
session_start();

require_once __DIR__ . '/../../Application/Services/Auth/login_user.php';
require_once __DIR__ . '/../../Application/Services/Rides/ManageRides.php';

if (!LoginUser::isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// ---- Logout (menú) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    LoginUser::logout();
    header('Location: ../auth/login.php');
    exit;
}

$rideId = (int) ($_GET['id'] ?? 0);
$ride = ManageRides::getRide($rideId); // asumiendo que ya tienes este método
if (!$ride) {
    http_response_code(404);
    echo "Ride no encontrado";
    exit;
}

// Datos
$origin = (string) $ride['origin'];
$destination = (string) $ride['destination'];
$veh = trim(($ride['make'] ?? '') . ' ' . ($ride['model'] ?? '') . ' ' . ($ride['year'] ?? ''));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ride Details - AVENTONES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/rides.css">
    <link rel="stylesheet" href="../../../public/assets/css/ridedetails.css"><!-- opcional -->

    <!-- Leaflet (mismo stack del proyecto pasado) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        /* Asegura altura visible del mapa sin tocar tus CSS globales */
        #map {
            height: 380px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        /* Oculta los combos “puente” que usa apimaps.js */
        .ghost-select {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        /* Ajusta la posición del bloque de botones bajo el mapa */
        .card.p-3 .mt-3.d-flex {
            margin-top: 10px !important;
            /* reduce el espacio vertical */
            justify-content: center;
            /* centra horizontalmente los botones */
        }

        /* (opcional) ajusta el tamaño de los botones */
        .card.p-3 .mt-3.d-flex .btn {
            padding: 6px 16px;
            font-size: 0.95rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </div>

    <!-- Menú unificado -->
    <div class="menu-container">
        <div class="menu">
            <nav class="left-menu">
                <a href="../rides/searchrides.php">Home</a>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="container">

        <h2 class="mb-2"><?= htmlspecialchars($ride['name'] ?? 'Ride #' . $rideId) ?></h2>

        <div class="grid-2">
            <!-- Columna izquierda: datos -->
            <div class="card p-3">
                <div class="row">
                    <div class="col">
                        <p><strong>Origen:</strong> <?= htmlspecialchars($origin) ?></p>
                        <p><strong>Destino:</strong> <?= htmlspecialchars($destination) ?></p>
                        <p><strong>Días:</strong> <?= htmlspecialchars($ride['days']) ?></p>
                        <p><strong>Hora:</strong> <?= htmlspecialchars(substr((string) $ride['time'], 0, 5)) ?></p>
                    </div>
                    <div class="col">
                        <p><strong>Vehículo:</strong> <?= htmlspecialchars($veh) ?></p>
                        <p><strong>Asientos:</strong> <?= (int) $ride['seats_total'] ?></p>
                        <p><strong>Tarifa:</strong> ₡
                            <?= htmlspecialchars(number_format((float) $ride['seat_price'], 2)) ?>
                        </p>

                        <!-- Mostrar foto del vehículo si existe -->
                        <h5 class="mt-3">Vehicle</h5>
                        <?php if (!empty($ride['photo_path'])): ?>
                            <div class="text-center mb-3">
                                <img src="../../../public/<?= htmlspecialchars($ride['photo_path']) ?>" alt="Vehicle Photo"
                                    class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        <?php else: ?>
                            <div class="text-center mb-3">
                                <img src="../../../public/assets/img/avatar.png" alt="No Vehicle Photo"
                                    class="img-fluid rounded" style="max-height: 200px; opacity: 0.5;">
                                <p class="text-muted mt-2">No vehicle photo available</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- Columna derecha: mapa -->
            <div class="card p-3">
                <h3 class="mb-1">Ruta</h3>

                <!-- “Puente” para apimaps.js: selects #from y #to con los valores del ride -->
                <select id="from" class="ghost-select" aria-hidden="true">
                    <option value="<?= htmlspecialchars($origin) ?>" selected><?= htmlspecialchars($origin) ?></option>
                </select>

                <select id="to" class="ghost-select" aria-hidden="true">
                    <option value="<?= htmlspecialchars($destination) ?>" selected><?= htmlspecialchars($destination) ?>
                    </option>
                </select>

                <!-- Contenedor del mapa (usado por apimaps.js) -->
                <small class="text-muted">Vista aproximada de la ruta entre origen y destino.</small>
                <div id="map"></div>

                <div class="mt-3 d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="#"
                        onclick="event.preventDefault(); history.back();">Volver</a>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'driver'): ?>
                        <a class="btn btn-primary" href="../myrides/editride.php?id=<?= $rideId ?>">Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet + apimaps (solo estos JS, como pediste) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Ajusta la ruta del JS según donde ubiques el archivo del proyecto pasado -->
    <script src="../../../public/assets/js/home/apimaps.js"></script>
</body>

</html>