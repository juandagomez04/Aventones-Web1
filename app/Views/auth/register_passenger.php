<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Usuarios - Aventones</title>
  <link rel="stylesheet" href="../../../public/assets/css/base.css">
  <link rel="stylesheet" href="../../../public/assets/css/auth.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="auth">
  <!-- Main container -->
  <div class="container">

    <!-- Header -->
    <div class="header">
      <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
      <h1 class="title">AVENTONES</h1>
    </div>

    <h2 class="subtitle">User Registration</h2>

    <!-- Registration Form -->
    <form class="form" action="login.php" method="post">

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
        <label for="photo">Photo</label>
        <input type="file" id="photo" name="photo" accept="image/*">
      </div>
      
      <div class="field">
        <label for="address">Address</label>
        <input type="text" id="address" name="address" required>
      </div>
      
      <div class="row">
        <div class="field">
          <label for="country">Country</label>
          <select id="country" name="country" required>
            <option value="">Select Country</option>
            <option value="CR">Costa Rica</option>
            <option value="MX">México</option>
          </select>
        </div>
        
        <div class="field">
          <label for="state">State</label>
          <input type="text" id="state" name="state" required>
        </div>
      </div>
      
      <div class="row">
        <div class="field">
          <label for="city">City</label>
          <input type="text" id="city" name="city" required>
        </div>
        
        <div class="field">
          <label for="phone">Phone Number</label>
          <input type="text" id="phone" name="phone" required>
        </div>
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
      
      <!-- Links -->
      <div class="links">
        <p class="login-link">Already a user? <a href="login.php">Login here</a></p>
        <p class="register-driver-link">Register as driver? <a href="register_drivers.php">Click here</a></p>
      </div>

      <!-- Submit -->
      <button type="submit" class="submit-btn">Sign up</button>
    </form>

    <!-- Footer -->
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