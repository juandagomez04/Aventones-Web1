<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Conductor - Aventones</title>
  <link rel="stylesheet" href="../../../public/assets/css/base.css">
  <link rel="stylesheet" href="../../../public/assets/css/auth.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="auth">
  <div class="container">

    <!-- Header with logo and title -->
    <div class="header">
      <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
      <h1 class="title">AVENTONES</h1>
    </div>

    <!-- Page subtitle -->
    <h2 class="subtitle">Driver Registration</h2>

    <!-- Driver registration form -->
    <form class="form" action="../auth/login.php" method="post">

      <!-- Row: First and Last Name -->
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

      <!-- Field: National ID -->
      <div class="field">
        <label for="cedula">National ID (Cédula)</label>
        <input type="text" id="cedula" name="cedula" maxlength="12" required>
      </div>

      <!-- Field: Date of Birth -->
      <div class="field">
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" required>
      </div>

      <div class="field">
        <label for="photo">Photo</label>
        <input type="file" id="photo" name="photo" accept="image/*">
      </div>

      <!-- Row: Email and Phone -->
      <div class="row">
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="field">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" pattern="[0-9]{8,15}" placeholder="e.g., 88888888" required>
        </div>
      </div>

      <!-- Field: Password -->
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <!-- Field: Repeat Password -->
      <div class="field">
        <label for="repeat">Repeat Password</label>
        <input type="password" id="repeat" name="repeat" required>
      </div>

      <!-- Row: Vehicle brand and model -->
      <div class="row">
        <div class="field">
          <label for="brand">Vehicle Brand</label>
          <input type="text" id="brand" name="brand" required>
        </div>
        <div class="field">
          <label for="model">Vehicle Model</label>
          <input type="text" id="model" name="model" required>
        </div>
      </div>

      <!-- Row: Year and license plate -->
      <div class="row">
        <div class="field">
          <label for="year">Year</label>
          <input type="number" id="year" name="year" min="1980" max="2025" required>
        </div>
        <div class="field">
          <label for="plate">License Plate</label>
          <input type="text" id="plate" name="plate" required>
        </div>
      </div>

      <!-- Links for login and passenger registration -->
      <div class="links">
        <p class="login-link">Already a driver? <a href="login.php">Login here</a></p>
        <p class="register-driver-link">Register as user? <a href="register_passenger.php">Click here</a></p>
      </div>

      <!-- Submit button -->
      <button type="submit" class="submit-btn">Register</button>
    </form>

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
  </div>

  <!-- Ruta correcta al JS -->
  <script src="../../../public/assets/js/login-register.js"></script>
</body>

</html>