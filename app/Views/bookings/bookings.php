<?php
/* app/Views/bookings/bookings.php */
session_start();

require_once __DIR__ . '/../../Application/Services/Auth/login_user.php';
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
$flash = null;

/* ==== Acciones POST delegadas al Service ==== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' && $role === 'passenger') {
            $rideId = (int) $_POST['ride_id'];
            ManageBookings::createBooking($rideId, $userId);
            $flash = ['success', 'Reserva creada (PENDING)'];
        } elseif ($action === 'accept' && $role === 'driver') {
            $bookingId = (int) $_POST['booking_id'];
            ManageBookings::updateBookingStatus($bookingId, 'accepted', $userId, 'driver');
            $flash = ['success', 'Reserva aceptada'];
        } elseif ($action === 'reject' && $role === 'driver') {
            $bookingId = (int) $_POST['booking_id'];
            ManageBookings::updateBookingStatus($bookingId, 'rejected', $userId, 'driver');
            $flash = ['warning', 'Reserva rechazada'];
        } elseif ($action === 'cancel' && $role === 'passenger') {
            $bookingId = (int) $_POST['booking_id'];
            ManageBookings::updateBookingStatus($bookingId, 'cancelled', $userId, 'passenger');
            $flash = ['info', 'Reserva cancelada'];
        }
    } catch (Throwable $e) {
        $flash = ['danger', $e->getMessage()];
    }
}

/* ==== Listado según rol ==== */
$rows = ($role === 'driver')
    ? ManageBookings::getBookingsByDriver($userId)
    : ManageBookings::getBookingsByPassenger($userId);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Bookings - AVENTONES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/bookings.css">
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
                <a href="../rides/searchrides.php">Home</a>
                <a href="../myrides/myrides.php">Rides</a>
                <a class="active" href="../bookings/bookings.php">Bookings</a>
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

    <div class="container">

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash[0]) ?>"><?= htmlspecialchars($flash[1]) ?></div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ride</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Días</th>
                        <th>Hora</th>
                        <th>Vehículo</th>
                        <th>Status</th>
                        <th>Asientos</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row):
                        $rideId = (int) $row['ride_id'];
                        $veh = trim(($row['make'] ?? '') . ' ' . ($row['model'] ?? '') . ' ' . ($row['year'] ?? ''));
                        $avail = ManageBookings::getAvailableSeats($rideId);

                        $status = strtolower((string) $row['status']);
                        $label = ucfirst($status);
                        $cls = 'badge-secondary';
                        if ($status === 'pending')
                            $cls = 'badge-warning';
                        if ($status === 'accepted')
                            $cls = 'badge-success';
                        if ($status === 'rejected')
                            $cls = 'badge-danger';
                        if ($status === 'cancelled')
                            $cls = 'badge-muted';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name'] ?? ('#' . $rideId)) ?></td>
                            <td><?= htmlspecialchars($row['origin']) ?></td>
                            <td><?= htmlspecialchars($row['destination']) ?></td>
                            <td><?= htmlspecialchars($row['days']) ?></td>
                            <td><?= htmlspecialchars(substr((string) $row['time'], 0, 5)) ?></td>
                            <td><?= htmlspecialchars($veh) ?></td>
                            <td><span class="badge <?= $cls ?>"><?= $label ?></span></td>
                            <td><?= (int) $avail ?> / <?= (int) $row['seats_total'] ?></td>
                            <td class="text-end">
                                <?php if ($role === 'driver'): ?>
                                    <?php if ($status === 'pending'): ?>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="action" value="accept">
                                            <input type="hidden" name="booking_id" value="<?= (int) $row['id'] ?>">
                                            <button class="btn btn-sm btn-success">Aceptar</button>
                                        </form>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="booking_id" value="<?= (int) $row['id'] ?>">
                                            <button class="btn btn-sm btn-danger">Rechazar</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline" disabled>Sin acciones</button>
                                    <?php endif; ?>
                                <?php else: /* passenger */ ?>
                                    <?php if (in_array($status, ['pending', 'accepted'], true)): ?>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="booking_id" value="<?= (int) $row['id'] ?>">
                                            <button class="btn btn-sm btn-warning">Cancelar</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline" disabled>Sin acciones</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No tienes reservas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>