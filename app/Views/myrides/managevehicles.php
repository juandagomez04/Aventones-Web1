<?php
// /Proyecto/app/Views/myrides/managevehicles.php
session_start();
require_once __DIR__ . '/../../Application/Services/Rides/ManageVehicles.php';

// Verificar que el usuario esté logueado y sea conductor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'driver') {
    header('Location: ../auth/login.php');
    exit;
}

$driverId = $_SESSION['user_id'];
$vehicles = ManageVehicles::getVehiclesByDriver($driverId);
$editingVehicle = null;

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $vehicleData = [
                'driver_id' => $driverId,
                'plate' => $_POST['plate'],
                'color' => $_POST['color'],
                'make' => $_POST['make'],
                'model' => $_POST['model'],
                'year' => $_POST['year'],
                'seats_capacity' => $_POST['seats_capacity']
            ];

            if ($action === 'create') {
                ManageVehicles::createVehicle($vehicleData, $_FILES);
                $message = "✅ Vehículo creado exitosamente.";
            } else {
                $vehicleId = (int) $_POST['vehicle_id'];
                $vehicleData['vehicle_id'] = $vehicleId;
                ManageVehicles::updateVehicle($vehicleId, $vehicleData, $_FILES);
                $message = "✅ Vehículo actualizado exitosamente.";
            }

            // Recargar la página para ver los cambios
            header('Location: managevehicles.php?message=' . urlencode($message));
            exit;
        }

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Procesar acciones GET (editar, eliminar)
if (isset($_GET['action'])) {
    try {
        if ($_GET['action'] === 'edit' && isset($_GET['id'])) {
            $editingVehicle = ManageVehicles::getVehicleById((int) $_GET['id'], $driverId);
            if (!$editingVehicle) {
                $error = "Vehículo no encontrado.";
            }
        } elseif ($_GET['action'] === 'delete' && isset($_GET['id'])) {
            // Mostrar confirmación antes de eliminar
            if (!isset($_GET['confirm'])) {
                $vehicleToDelete = ManageVehicles::getVehicleById((int) $_GET['id'], $driverId);
                if ($vehicleToDelete) {
                    $showDeleteConfirmation = true;
                    $vehicleToDeleteId = (int) $_GET['id'];
                    $vehicleToDeletePlate = $vehicleToDelete['plate'];
                }
            } else {
                // Confirmación recibida, proceder con eliminación
                ManageVehicles::deleteVehicle((int) $_GET['id'], $driverId);
                $message = "✅ Vehículo eliminado exitosamente.";
                header('Location: managevehicles.php?message=' . urlencode($message));
                exit;
            }
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Mensajes de éxito
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Base URL para las rutas
$baseUrl = '/Proyecto%201/public/';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vehículos - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/base.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/vehicles.css">
    <style>
        .confirmation-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
        }

        .confirmation-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
    </style>
</head>

<body class="auth">
    <!-- Modal de confirmación para eliminar -->
    <?php if (isset($showDeleteConfirmation) && $showDeleteConfirmation): ?>
        <div class="confirmation-modal">
            <div class="confirmation-content">
                <h4>¿Estás seguro?</h4>
                <p>¿Deseas eliminar el vehículo con placa <strong><?= htmlspecialchars($vehicleToDeletePlate) ?></strong>?
                </p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                <div class="mt-4">
                    <a href="?action=delete&id=<?= $vehicleToDeleteId ?>&confirm=1" class="btn btn-danger">Sí, eliminar</a>
                    <a href="managevehicles.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="header">
            <img src="<?= $baseUrl ?>assets/img/Icono.png" alt="Logo" class="logo">
            <h1 class="title">AVENTONES</h1>
        </div>

        <h2 class="subtitle">Gestión de Vehículos</h2>

        <!-- Mensajes -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Tabla de vehículos -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">Mis Vehículos</h3>
            </div>
            <div class="card-body">
                <?php if (empty($vehicles)): ?>
                    <p class="text-muted">No tienes vehículos registrados.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Placa</th>
                                    <th>Marca</th>
                                    <th>Modelo</th>
                                    <th>Año</th>
                                    <th>Color</th>
                                    <th>Asientos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <tr>
                                        <td>
                                            <?php if ($vehicle['photo_path']): ?>
                                                <img src="<?= $baseUrl . htmlspecialchars($vehicle['photo_path']) ?>" alt="Vehículo"
                                                    class="vehicle-photo"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                                <span class="text-muted" style="display: none;">Sin foto</span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin foto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($vehicle['plate']) ?></td>
                                        <td><?= htmlspecialchars($vehicle['make']) ?></td>
                                        <td><?= htmlspecialchars($vehicle['model']) ?></td>
                                        <td><?= htmlspecialchars($vehicle['year']) ?></td>
                                        <td><?= htmlspecialchars($vehicle['color']) ?></td>
                                        <td><?= htmlspecialchars($vehicle['seats_capacity']) ?></td>
                                        <td class="table-actions">
                                            <a href="?action=edit&id=<?= $vehicle['id'] ?>"
                                                class="btn btn-sm btn-warning">Editar</a>
                                            <a href="?action=delete&id=<?= $vehicle['id'] ?>"
                                                class="btn btn-sm btn-danger">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario para crear/editar vehículo -->
        <div class="form-section">
            <h3><?= $editingVehicle ? 'Editar Vehículo' : 'Agregar Nuevo Vehículo' ?></h3>

            <form method="post" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="action" value="<?= $editingVehicle ? 'update' : 'create' ?>">
                <?php if ($editingVehicle): ?>
                    <input type="hidden" name="vehicle_id" value="<?= $editingVehicle['id'] ?>">
                <?php endif; ?>

                <div class="col-md-6">
                    <label for="plate" class="form-label">Placa *</label>
                    <input type="text" class="form-control" id="plate" name="plate"
                        value="<?= $editingVehicle ? htmlspecialchars($editingVehicle['plate']) : '' ?>" required
                        maxlength="15">
                </div>

                <div class="col-md-6">
                    <label for="color" class="form-label">Color *</label>
                    <input type="text" class="form-control" id="color" name="color"
                        value="<?= $editingVehicle ? htmlspecialchars($editingVehicle['color']) : '' ?>" required
                        maxlength="30">
                </div>

                <div class="col-md-6">
                    <label for="make" class="form-label">Marca *</label>
                    <input type="text" class="form-control" id="make" name="make"
                        value="<?= $editingVehicle ? htmlspecialchars($editingVehicle['make']) : '' ?>" required
                        maxlength="50">
                </div>

                <div class="col-md-6">
                    <label for="model" class="form-label">Modelo *</label>
                    <input type="text" class="form-control" id="model" name="model"
                        value="<?= $editingVehicle ? htmlspecialchars($editingVehicle['model']) : '' ?>" required
                        maxlength="50">
                </div>

                <div class="col-md-6">
                    <label for="year" class="form-label">Año *</label>
                    <input type="number" class="form-control" id="year" name="year"
                        value="<?= $editingVehicle ? htmlspecialchars($editingVehicle['year']) : '' ?>" min="1990"
                        max="<?= date('Y') + 1 ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="seats_capacity" class="form-label">Capacidad de Asientos *</label>
                    <input type="number" class="form-control" id="seats_capacity" name="seats_capacity"
                        value="<?= $editingVehicle ? htmlspecialchars($editingVehicle['seats_capacity']) : '' ?>"
                        min="1" max="20" required>
                </div>

                <div class="col-12">
                    <label for="photo" class="form-label">Fotografía del Vehículo</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                    <?php if ($editingVehicle && $editingVehicle['photo_path']): ?>
                        <div class="mt-2">
                            <small>Foto actual:</small><br>
                            <img src="<?= $baseUrl . htmlspecialchars($editingVehicle['photo_path']) ?>" alt="Vehículo"
                                style="max-width: 200px; max-height: 150px;" class="mt-1 current-photo"
                                onerror="this.style.display='none';">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <?= $editingVehicle ? 'Actualizar Vehículo' : 'Crear Vehículo' ?>
                    </button>
                    <?php if ($editingVehicle): ?>
                        <a href="managevehicles.php" class="btn btn-secondary">Cancelar</a>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" onclick="clearForm()">Limpiar Campos</button>
                    <?php endif; ?>
                    <a class="btn btn-outline-secondary" href="myrides.php">Back to My Rides</a>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <footer class="mt-4">
            <hr>
            <nav class="text-center">
                <a href="myrides.php">Mis Viajes</a> |
                <a href="../rides/searchrides.php">Buscar Viajes</a> |
                <a href="../profile/configuration.php">Configuración</a> |
                <a href="../auth/login.php">Cerrar Sesión</a>
            </nav>
            <p class="text-center text-muted">&copy; <?= date('Y') ?> Aventones.com</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function clearForm() {
            document.getElementById('plate').value = '';
            document.getElementById('color').value = '';
            document.getElementById('make').value = '';
            document.getElementById('model').value = '';
            document.getElementById('year').value = '';
            document.getElementById('seats_capacity').value = '';
            document.getElementById('photo').value = '';
        }
    </script>
</body>

</html>