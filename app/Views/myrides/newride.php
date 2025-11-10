<?php
/* Proyecto 1/app/Views/myrides/newride.php */

require_once __DIR__ . '/../../Application/Services/Rides/ManageRides.php';

// Suponemos que el login puso el id en $_SESSION['user_id']
session_start();
$driverId = (int) ($_SESSION['user_id'] ?? 0);

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        ManageRides::createRide($driverId, $_POST);
        $msg = ['ok', "Ride created!"];
    } catch (Throwable $e) {
        $msg = ['err', $e->getMessage()];
    }
}

// Vehículos del driver para el select
$veh = $pdo->prepare("SELECT id, plate, make, model FROM vehicles WHERE driver_id=? ORDER BY id DESC");
$veh->execute([$driverId]);
$vehicles = $veh->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>New Ride - Aventones</title>
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/rides.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-4">
        <h2 class="subtitle">New Ride</h2>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg[0] === 'ok' ? 'success' : 'danger' ?>"><?= htmlspecialchars($msg[1]) ?></div>
        <?php endif; ?>

        <form method="post" class="card card-body gap-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">Select vehicle</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>">
                                <?= htmlspecialchars($v['plate'] . ' - ' . $v['make'] . ' ' . $v['model']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ride name</label>
                    <input name="name" class="form-control" required maxlength="80">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Departure from</label>
                    <input name="origin" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Arrive to</label>
                    <input name="destination" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">Days</label>
                    <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d): ?>
                        <label class="me-3"><input type="checkbox" name="days[]" value="<?= $d ?>"> <?= $d ?></label>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Time</label>
                    <input type="time" name="time" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Seat price</label>
                    <input type="number" step="0.01" min="0" name="seat_price" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Seats</label>
                    <input type="number" min="1" name="seats_total" class="form-control" required>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary">Create</button>
                <a class="btn btn-outline-secondary" href="myrides.php">Back to My Rides</a>
            </div>
        </form>
    </div>
</body>

</html>