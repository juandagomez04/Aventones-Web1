<?php
// /Proyecto/app/Views/auth/register_passenger.php
require_once __DIR__ . '/../../Application/Services/Auth/register.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mapear nombres del form a los que espera RegisterService
    $map = [
        'fname' => 'first_name',
        'lname' => 'last_name', 
        'cedula' => 'national_id',
        'dob' => 'birth_date',
        'repeat' => 'password2',
    ];
    
    foreach ($map as $from => $to) {
        if (isset($_POST[$from]) && !isset($_POST[$to])) {
            $_POST[$to] = $_POST[$from];
        }
    }
    
    try {
        RegisterService::registerPassenger($_POST, $_FILES);
        echo "<script>alert('✅ Registro exitoso. Revisa tu correo para activar la cuenta.'); window.location.href='login.php';</script>";
        exit;
    } catch (Throwable $e) {
        echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Usuarios - Aventones</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../public/assets/css/base.css">
  <link rel="stylesheet" href="../../../public/assets/css/auth.css">
</head>

<body class="auth">
  <div class="container">
    <div class="header">
      <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
      <h1 class="title">AVENTONES</h1>
    </div>

    <h2 class="subtitle">User Registration</h2>

    <!-- Registration Form -->
    <form class="form" action="register_passenger.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="register_type" value="passenger">

      <!-- Campos que van al execute() -->
      <div class="row">
        <div class="field">
          <label for="fname">First Name</label>
          <input type="text" id="fname" name="fname" required>
        </div>
        <div class="field">
          <label for="lname">Last Name</label>
          <input type="text" id="lname" name="lname" required>
        </div>
      </div>

      <div class="field">
        <label for="cedula">National ID (Cédula)</label>
        <input type="text" id="cedula" name="cedula" maxlength="12" required>
      </div>

      <div class="field">
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" required>
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="row">
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="field">
          <label for="repeat">Repeat Password</label>
          <input type="password" id="repeat" name="repeat" required>
        </div>
      </div>

      <div class="field">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required>
      </div>

      <div class="field">
        <label for="photo">Photo</label>
        <input type="file" id="photo" name="photo" accept="image/*">
      </div>


      <!-- Links -->
      <div class="links">
        <p class="login-link">Already a user? <a href="login.php">Login here</a></p>
        <p class="register-driver-link">Register as driver? <a href="register_drivers.php">Click here</a></p>
      </div>

      <button type="submit" class="submit-btn">Sign up</button>
    </form>

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
  </div>
</body>
</html>