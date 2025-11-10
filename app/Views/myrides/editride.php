<?php
/* Proyecto 1/app/Views/myrides/editride.php */

require_once __DIR__ . '/../../Application/Services/Rides/ManageRides.php';
session_start();
$driverId = (int) ($_SESSION['user_id'] ?? 0);
$rideId = (int) ($_GET['id'] ?? 0);

$ride = ManageRides::getRide($rideId);
if (!$ride) {
    die('Ride not found');
}

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        ManageRides::updateRide($driverId, $rideId, $_POST);
        $ride = ManageRides::getRide($rideId); // refrescar
        $msg = ['ok', 'Ride updated!'];
    } catch (Throwable $e) {
        $msg = ['err', $e->getMessage()];
    }
}

// Vehículos del driver
$veh = $pdo->prepare("SELECT id, plate, make, model FROM vehicles WHERE driver_id=? ORDER BY id DESC");
$veh->execute([$driverId]);
$vehicles = $veh->fetchAll(PDO::FETCH_ASSOC);

$selectedDays = explode(',', (string) $ride['days']);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Edit Ride</title>
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-4">
        <h2 class="subtitle">Edit Ride</h2>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg[0] === 'ok' ? 'success' : 'danger' ?>"><?= htmlspecialchars($msg[1]) ?></div>
        <?php endif; ?>

        <form method="post" class="card card-body gap-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select" required>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= $v['id'] == $ride['vehicle_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['plate'] . ' - ' . $v['make'] . ' ' . $v['model']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ride name</label>
                    <input name="name" class="form-control" required maxlength="80"
                        value="<?= htmlspecialchars($ride['name']) ?>">
                </div>
                <div class="col-md-6"><label class="form-label">Departure from</label>
                    <input name="origin" class="form-control" required value="<?= htmlspecialchars($ride['origin']) ?>">
                </div>
                <div class="col-md-6"><label class="form-label">Arrive to</label>
                    <input name="destination" class="form-control" required
                        value="<?= htmlspecialchars($ride['destination']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">Days</label>
                    <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d): ?>
                        <label class="me-3">
                            <input type="checkbox" name="days[]" value="<?= $d ?>"
                                <?= in_array($d, $selectedDays) ? 'checked' : '' ?>> <?= $d ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-3"><label class="form-label">Time</label>
                    <input type="time" name="time" class="form-control" required
                        value="<?= substr($ride['time'], 0, 5) ?>">
                </div>
                <div class="col-md-3"><label class="form-label">Seat price</label>
                    <input type="number" step="0.01" min="0" name="seat_price" class="form-control" required
                        value="<?= htmlspecialchars($ride['seat_price']) ?>">
                </div>
                <div class="col-md-3"><label class="form-label">Seats</label>
                    <input type="number" min="1" name="seats_total" class="form-control" required
                        value="<?= (int) $ride['seats_total'] ?>">
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary">Save changes</button>
                <a class="btn btn-outline-secondary" href="myrides.php">Back to My Rides</a>
            </div>
        </form>
    </div>
</body>

</html>