<?php
/* Proyecto 1/app/Views/myrides/myrides.php */
session_start();

require_once __DIR__ . '/../../Application/Services/Auth/login_user.php';
require_once __DIR__ . '/../../Application/Services/Rides/ManageRides.php';


// ---- Logout ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    LoginUser::logout();
    header('Location: ../auth/login.php');
    exit;
}

// ---- Guardas ----
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (($_SESSION['user_role'] ?? '') !== 'driver') {
    header('Location: ../auth/login.php');
    exit;
}

$driverId = (int) $_SESSION['user_id'];

// ---- Eliminar ride ----
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    try {
        $ok = ManageRides::deleteRide($driverId, (int) $_POST['ride_id']);
    } catch (Throwable $e) {
        $flash = ['danger', $e->getMessage()];
    }
}

// ---- Listar rides del driver ----
try {
    $rides = ManageRides::listByDriver($driverId);
} catch (Throwable $e) {
    $rides = [];
    $flash = ['danger', 'Error loading rides: ' . $e->getMessage()];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Rides - AVENTONES</title>
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/rides.css">
    <style>
        /* === Tabla de "My Rides" específica === */
        table.table-rides {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px;
            margin: 0;
            background: transparent;
            box-shadow: none;
            border-radius: 0;
            overflow: visible;
            font-size: 14px;
        }

        /* Cabecera */
        table.table-rides thead {
            background: #f8f9fa;
            font-weight: 600;
        }

        table.table-rides th {
            padding: 20px 25px;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            background: #f8f9fa;
        }

        /* Celdas */
        table.table-rides td {
            padding: 28px 25px;
            background: #ffffff;
            border: none;
            font-size: 14px;
            color: #495057;
            vertical-align: middle;
            line-height: 1.5;
        }

        /* Fila tipo "tarjeta" */
        table.table-rides tbody tr {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            transition: all 0.3s ease;
            background-color: #ffffff;
        }

        table.table-rides tbody tr:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        /* === Columna de acciones === */
        table.table-rides td.actions {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            padding-right: 6px;
        }

        /* Formularios dentro de acciones en línea */
        table.table-rides td.actions form {
            display: inline-block;
            margin: 0;
        }

        /* Botones de acción (detalles, editar, eliminar) */
        table.table-rides td.actions .btn,
        table.table-rides td.actions a.btn,
        table.table-rides td.actions form .btn,
        table.table-rides td.actions button,
        table.table-rides td.actions input[type="submit"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            height: 30px;
            padding: 6px 12px;
            line-height: 1;
            font-size: 12px;
            font-weight: 500;
            border-radius: 4px;
            background: #007bff;
            border: 1px solid #007bff;
            color: #fff;
            text-decoration: none;
            box-shadow: 0 1px 4px rgba(13, 110, 253, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        /* Hover para todos los botones */
        table.table-rides td.actions .btn:hover,
        table.table-rides td.actions a.btn:hover,
        table.table-rides td.actions form .btn:hover,
        table.table-rides td.actions button:hover,
        table.table-rides td.actions input[type="submit"]:hover {
            background: #0056b3;
            border-color: #0056b3;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.25);
            transform: translateY(-1px);
        }

        /* Mensaje cuando no hay rides */
        table.table-rides .text-muted {
            color: #6c757d;
            text-align: center;
            padding: 50px;
            font-style: italic;
            font-size: 16px;
            border-bottom: none;
        }
    </style>

</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </div>

    <!-- Top menu -->
    <div class="menu-container">
        <div class="menu">
            <nav class="left-menu">
                <a href="../rides/searchrides.php">Home</a>
                <a class="active" href="../myrides/myrides.php">Rides</a>
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

    <hr>

    <main>

        <div class="container my-3">
            <h2 class="subtitle">My rides</h2>

            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash[0]) ?>"><?= htmlspecialchars($flash[1]) ?></div>
            <?php endif; ?>

            <div class="mb-3 d-flex gap-2">
                <a href="managevehicles.php" class="btn btn-outline-secondary">Manage Vehicles</a>
                <a href="newride.php" class="btn btn-primary">New Ride</a>
            </div>

            <div class="table-rides-container">
                <table class="table-rides">
                    <thead>
                        <tr>
                            <th>Ride</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Time</th>
                            <th>Seats</th>
                            <th>Price</th>
                            <th>Car</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rides):
                            foreach ($rides as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['name']) ?></td>
                                    <td><?= htmlspecialchars($r['origin']) ?></td>
                                    <td><?= htmlspecialchars($r['destination']) ?></td>
                                    <td><?= htmlspecialchars($r['days']) ?></td>
                                    <td><?= substr($r['time'], 0, 5) ?></td>
                                    <td><?= (int) $r['seats_total'] ?></td>
                                    <td>₡<?= number_format((float) $r['seat_price'], 2) ?></td>
                                    <td><?= htmlspecialchars(($r['plate'] ?? '') . ' ' . ($r['make'] ?? '') . ' ' . ($r['model'] ?? '')) ?>
                                    </td>
                                    <td class="actions">
                                        <!-- Details -->
                                        <a class="btn" href="../rides/detailsride.php?id=<?= (int) $r['id'] ?>">Details</a>

                                        <!-- Edit -->
                                        <a class="btn" href="editride.php?id=<?= (int) $r['id'] ?>">Edit</a>

                                        <!-- Delete (se queda POST hacia esta misma página) -->
                                        <form method="post"
                                            onsubmit="return confirm('Are you sure you want to delete this ride?');"
                                            style="display:inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="ride_id" value="<?= (int) $r['id'] ?>">
                                            <button type="submit" class="btn">Delete</button>
                                        </form>
                                    </td>


                                </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="9" class="text-muted">You have no rides yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>


    <footer class="container">
        <hr>
        <nav>
            <a href="../rides/searchrides.php">Home</a> |
            <a href="../myrides/myrides.php">Rides</a> |
            <a href="../bookings/bookings.php">Bookings</a> |
            <a href="../profile/configuration.php">Settings</a> |
            <a href="../auth/login.php">Login</a> |
            <a href="../auth/register_passenger.php">Register</a>
        </nav>
        <p>&copy; Aventones.com</p>
    </footer>
</body>

</html>