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
            <!-- Google Sign-In Button -->
            <a class="google-btn" href="../myrides/myrides.php">
                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" class="google-icon">
                Sign in with Google
            </a>

            <div class="separator">Or</div>

            <!-- Username/Password Login Form -->
            <form action="#" method="POST">
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <!-- Registration Link -->
                <p class="register-link">
                    Not a user? <a href="register_passenger.php">Register Now</a>
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
            <a href="../myrides/myrides.php">Rides</a> |
            <a href="../bookings/bookings.php">Bookings</a> |
            <a href="../profile/configuration.php">Settings</a> |
            <a href="login.php">Login</a> |
            <a href="register_passenger.php">Register</a>
        </nav>
        <p>&copy; Aventones.com</p>
    </footer>

    <script src="../../../public/assets/js/login-auth.js"></script>
</body>

</html>