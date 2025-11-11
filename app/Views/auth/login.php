<?php
// login.php
session_start();

// Si ya está logueado, redirigir según su rol
if (isset($_SESSION['user_id']) && $_SESSION['user_status'] === 'active') {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../admin/administration.php');
    } else {
        header('Location: ../rides/searchrides.php');
    }
    exit;
}

// Procesar login si se envió el formulario
$error_message = '';
$info_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ruta corregida - ajusta según tu estructura de carpetas
    require_once '../../Application/Services/Auth/login_user.php';

    $email = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = LoginUser::authenticate($email, $password);

    if ($result['success']) {
        // Redirigir según el rol
        if ($result['role'] === 'admin') {
            header('Location: ../admin/administration.php');
        } else {
            header('Location: ../rides/searchrides.php');
        }
        exit;
    } else {
        $error_message = $result['message'];

        // Mensaje informativo específico para estado de cuenta
        if (LoginUser::isAccountPending($email)) {
            $info_message = "Your account is awaiting approval. You will be able to login once an administrator activates your account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/auth.css">
</head>

<body class="auth">
    <!-- Login Box Container -->
    <div class="login-box">
        <!-- Header with Logo and Title -->
        <div class="header">
            <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
            <h1 class="title">AVENTONES</h1>
        </div>

        <!-- Login Form Section -->
        <div class="login-form">
            <!-- Mostrar mensaje de error si existe -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- Google Sign-In Button -->
            <a class="google-btn" href="#">
                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" class="google-icon">
                Sign in with Google
            </a>

            <div class="separator">Or</div>

            <!-- Username/Password Login Form -->
            <form action="login.php" method="POST">
                <div class="field">
                    <label for="username">Email</label>
                    <input type="email" id="username" name="username" required
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <!-- Registration Links -->
                <p class="register-link">
                    Not a user?
                    <a href="register_passenger.php">Register as Passenger</a> |
                    <a href="register_drivers.php">Register as Driver</a>
                </p>

                <button type="submit" class="submit-btn login-btn">Login</button>
            </form>
        </div>
    </div>

    <!-- Footer with navigation links -->
    <footer>
        <hr>
        <nav>
            <a href="../rides/searchrides.php">Home</a> |
            <a href="../rides/searchrides.php">Rides</a> |
            <a href="../bookings/bookings.php">Bookings</a> |
            <a href="../profile/configuration.php">Settings</a> |
            <a href="login.php">Login</a> |
            <a href="register_passenger.php">Register</a>
        </nav>
        <p>&copy; Aventones.com</p>
    </footer>
</body>

</html>