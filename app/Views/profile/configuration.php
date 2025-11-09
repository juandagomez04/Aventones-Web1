<?php
require_once '../../Application/Services/Auth/login_user.php';

// Procesar logout SIEMPRE al inicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  LoginUser::logout();
  header('Location: ../auth/login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuration - AVENTONES</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../public/assets/css/base.css">
  <link rel="stylesheet" href="../../../public/assets/css/home.css">
</head>

<body>
  <!-- Header -->
  <header class="header">
    <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
    <h1 class="title">AVENTONES</h1>
  </header>

  <!-- Navigation bar -->
  <div class="menu-container">
    <div class="menu">
      <nav class="left-menu">
        <a href="../rides/searchrides.php">Home</a>
        <a href="../myrides/myrides.php">Rides</a>
        <a href="../bookings/bookings.php">Bookings</a>
      </nav>

      <div class="center-search">
        <input type="text" placeholder="Search..." class="search-bar">
      </div>

      <style>
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
      </style>

      <div class="right-menu">
        <div class="user-btn">
          <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
          <div class="dropdown-menu">
            <form method="POST">
              <button type="submit" name="logout" value="true" class="logout-btn">Logout</button>
            </form>
            <a href="../profile/editprofile.php">Profile</a>
            <a href="../profile/configuration.php" class="active">Settings</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr>

  <!-- Configuration container -->
  <main class="config-container">
    <h2>Configuration</h2>

    <form method="POST">
      <div class="field">
        <label for="fname">Public Name</label>
        <input type="text" id="fname" name="fname" value="Juanda" required>
      </div>

      <div class="field">
        <label for="bio">Public Bio</label>
        <textarea id="bio" name="bio" rows="5" placeholder="Tell us about yourself..."></textarea>
      </div>

      <div class="buttons">
        <a class="cancel-btn" onclick="window.history.back()">Cancel</a>
        <button type="submit" class="save-btn">Save</button>
      </div>
    </form>
  </main>

  <!-- Footer -->
  <footer>
    <hr>
    <nav>
      <a href="../rides/searchrides.php">Home</a> |
      <a href="../myrides/myrides.php">Rides</a> |
      <a href="../bookings/bookings.php">Bookings</a> |
      <a href="../profile/configuration.php">Settings</a> |
      <a href="../auth/login.php">Login</a> |
      <a href="../auth/register_passenger.php">Register</a>
    </nav>
    <p>&copy; Aventones.com</p>
  </footer>
</body>

</html>