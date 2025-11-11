<?php
session_start();
require_once '../../Application/Services/Auth/login_user.php';

// Procesar logout SIEMPRE al inicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    LoginUser::logout();
    header('Location: ../auth/login.php');
    exit;
}

// Attempt to include AdminActions from known relative locations to avoid path issues on different environments
$possible = [
    dirname(__DIR__, 2) . '/Application/Services/Admin/AdminActions.php', // app/Application/...
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

$msg = null; // Variable para mensaje

// Obtener filtros actuales
$roleFilter = $_GET['role'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';

// ---- Acciones (POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            AdminActions::createAdmin($_POST, $_FILES['photo'] ?? null);
            $_SESSION['alert'] = "✅ Admin created successfully!";
        } elseif ($action === 'delete') {
            $_SESSION['alert'] = !AdminActions::deleteAdmin((int) $_POST['id'])
                ? "⚠️ You cannot delete the first (seed) admin or leave the system without admins."
                : "🗑️ Admin deleted successfully!";
        } elseif ($action === 'update_status') {
            $success = AdminActions::updateUserStatus((int) $_POST['id'], $_POST['status']);
            $_SESSION['alert'] = $success
                ? "✅ User status updated successfully!"
                : "⚠️ Cannot desactivate the last active admin.";
        }
    } catch (Throwable $e) {
        $_SESSION['alert'] = "❌ Error: " . $e->getMessage();
    }

    // PRG: redirige tras procesar para evitar banners y reenvíos
    header('Location: administration.php?' . http_build_query(['role' => $roleFilter, 'status' => $statusFilter]));
    exit;
}

// ---- GET: si hay alerta en sesión, mostrar SOLO alert() ----
if (!empty($_SESSION['alert'])) {
    echo "<script>window.onload = function(){ alert(" . json_encode($_SESSION['alert']) . "); };</script>";
    unset($_SESSION['alert']);
}

// Cargar usuarios con filtros
$users = AdminActions::getUsers($roleFilter, $statusFilter);
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

    <style>
        .admin-top-menu {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0.5rem;
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .admin-top-menu .user-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            cursor: pointer;
        }

        .user-btn {
            cursor: pointer;
            position: relative;
            outline: none;
        }

        .user-icon {
            width: 40px;
            height: 40px;
            border-radius: 100%;
            display: block;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: calc(75% + 2px);
            right: 0;
            background-color: var(--color-white);
            border: 1px solid var(--color-border);
            min-width: 150px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            border-radius: 6px;
            overflow: hidden;
            white-space: nowrap;
        }

        .dropdown-menu form {
            margin: 0;
            padding: 0;
        }

        .dropdown-menu button.logout-btn {
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            padding: 15px;
            color: var(--color-text);
            cursor: pointer;
            font-size: inherit;
            font-family: inherit;
        }

        .dropdown-menu button.logout-btn:hover {
            background-color: var(--color-hover-bg);
        }

        .dropdown-menu a {
            color: var(--color-text);
            padding: 15px 15px;
            text-decoration: none;
            display: block;
        }

        .dropdown-menu a:hover {
            background-color: var(--color-hover-bg);
        }

        .user-btn:hover .dropdown-menu {
            display: block;
        }
    </style>

    <!-- Navigation bar - SOLO para admin -->
    <div class="admin-top-menu">
        <div class="user-btn" tabindex="0">
            <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
            <div class="dropdown-menu" role="menu" aria-hidden="true">
                <form method="POST">
                    <button type="submit" name="logout" value="true" class="logout-btn">Logout</button>
                </form>
                <a href="../profile/editprofile.php" role="menuitem">Profile</a>
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

        <!-- Filtros -->
        <div class="filters">
            <div class="field">
                <label>Filter by Role</label>
                <select name="role" onchange="updateFilters()">
                    <option value="all" <?= $roleFilter === 'all' ? 'selected' : '' ?>>All Roles</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Administrators</option>
                    <option value="driver" <?= $roleFilter === 'driver' ? 'selected' : '' ?>>Drivers</option>
                    <option value="passenger" <?= $roleFilter === 'passenger' ? 'selected' : '' ?>>Passengers</option>
                </select>
            </div>
            <div class="field">
                <label>Filter by Status</label>
                <select name="status" onchange="updateFilters()">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="table-card">
            <strong class="table-head">Users Management</strong>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge role <?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge status <?= $user['status'] ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(substr($user['created_at'], 0, 10)) ?></td>
                            <td>
                                <div class="actions">
                                    <?php if ($user['status'] === 'active'): ?>
                                        <form method="post" style="display:inline;"
                                            onsubmit="return confirm('Desactivate this user?')">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="link warn">Desactivate</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" style="display:inline;"
                                            onsubmit="return confirm('Activate this user?')">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="link ok">Activate</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($user['role'] === 'admin'): ?>
                                        <form method="post" style="display:inline;"
                                            onsubmit="return confirm('Delete this admin?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <button class="link warn">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
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

    <script>
        function updateFilters() {
            const role = document.querySelector('select[name="role"]').value;
            const status = document.querySelector('select[name="status"]').value;
            window.location.href = `administration.php?role=${role}&status=${status}`;
        }
    </script>

</body>

</html>