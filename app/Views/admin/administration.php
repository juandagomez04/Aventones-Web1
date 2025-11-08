<?php
// Attempt to include AdminActions from known relative locations to avoid path issues on different environments
$possible = [
    dirname(__DIR__, 2) . '/Application/Services/Admin/AdminActions.php', // app/Application/...
    dirname(__DIR__, 3) . '/Application/Services/Admin/AdminActions.php', // project_root/Application/...
];

$included = false;
foreach ($possible as $file) {
    if (file_exists($file)) {
        require_once $file;
        $included = true;
        break;
    }
}

if (!$included) {
    // Fallback to original path attempt and provide a clear error if not found
    $attempt = __DIR__ . '/../../../Application/Services/Admin/AdminActions.php';
    if (file_exists($attempt)) {
        require_once $attempt;
    } else {
        throw new RuntimeException("AdminActions.php not found. Tried: " . implode(', ', $possible) . ', ' . $attempt);
    }
}

// Crear admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        AdminActions::createAdmin($_POST, $_FILES['photo'] ?? null);
        $msg = "✅ Admin created successfully!";
    } catch (Throwable $e) {
        $msg = "❌ Error: " . $e->getMessage();
    }
}

// Eliminar admin
if (($_POST['action'] ?? '') === 'delete') {
    if (!AdminActions::deleteAdmin((int)$_POST['id'])) {
        $msg = 'You cannot delete the first (seed) admin or leave the system without admins.';
        $msgType = 'danger';
    } else {
        $msg = '🗑️ Admin deleted successfully!';
        $msgType = 'success';
    }
}


// Cargar admins
$admins = AdminActions::getAdmins();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AVENTONES - ADMINISTRATION</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">
</head>

<body>

    <header class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </header>

    <!-- Navigation bar -->
    <div class="menu-container">
        <div class="menu">
            

            <!-- Right user menu with dropdown -->
            <div class="right-menu">
                <div class="user-btn">
                    <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
                    <div class="dropdown-menu">
                        <a href="index.php">Logout</a>
                        <a href="../profile/configuration.php">Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="admin-wrap">
        <h2 class="subtitle">Administration</h2>

        <?php if (!empty($msg)): ?>
            <p class="alert"><?= htmlspecialchars($msg) ?></p>
        <?php endif; ?>

        <div class="admin-actions">
            <a href="#create-admin" class="btn primary">Create Admin</a>
        </div>

        <!-- Tabla de admins -->
        <div class="table-card">
            <strong class="table-head">Admins</strong>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><?= $a['id'] ?></td>
                            <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><?= htmlspecialchars($a['status']) ?></td>
                            <td><?= htmlspecialchars(substr($a['created_at'], 0, 10)) ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Delete this admin?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Crear admin -->
        <section id="create-admin" class="panel">
            <h3>Create Admin</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">

                <div class="row">
                    <div class="col-md-6">
                        <label>First Name</label>
                        <input name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Last Name</label>
                        <input name="last_name" class="form-control" required>
                    </div>
                </div>

                <label>Email</label>
                <input name="email" type="email" class="form-control" required>

                <label>Password</label>
                <input name="password" type="password" class="form-control" minlength="6" required>

                <label>Repeat Password</label>
                <input name="password2" type="password" class="form-control" minlength="6" required>

                <label>National ID</label>
                <input name="national_id" class="form-control">

                <label>Birth Date</label>
                <input name="birth_date" type="date" class="form-control">

                <label>Phone</label>
                <input name="phone" class="form-control">

                <label>Photo</label>
                <input name="photo" type="file" accept="image/*" class="form-control">

                <button type="submit" class="btn btn-primary mt-3">Create Admin</button>
            </form>
        </section>
    </main>

</body>

</html>