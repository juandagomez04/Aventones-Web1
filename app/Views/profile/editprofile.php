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
    <title>Edit Profile - AVENTONES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/home.css">
</head>

<body>
    <!-- Header with logo and site title -->
    <div class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </div>

    <!-- Top navigation menu -->
    <div class="menu-container">
        <div class="menu">
            <!-- Left navigation links -->
            <nav class="left-menu">
                <a href="../rides/searchrides.php">Home</a>
                <a href="../myrides/myrides">Rides</a>
                <a href="../bookings/bookings.php">Bookings</a>
            </nav>

            <!-- Centered search bar -->
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

            <!-- Right-side user icon and dropdown menu -->
            <div class="right-menu">
                <div class="user-btn">
                    <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
                    <div class="dropdown-menu">
                        <form method="POST">
                            <button type="submit" name="logout" value="true" class="logout-btn">Logout</button>
                        </form>
                        <a href="../profile/editprofile.php">Profile</a>
                        <a href="../profile/configuration.php">Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!-- Page subtitle -->
    <h2>Edit Profile</h2>

    <!-- Profile edit form -->
    <form class="form" action="../rides/searchrides.php" method="get">

        <!-- First and last name -->
        <div class="row">
            <div class="field">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="fname" value="Juanda" required>
            </div>
            <div class="field">
                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lname" value="Gómez" required>
            </div>
        </div>

        <div class="field">
            <label for="photo">Photo</label>
            <input type="file" id="photo" name="photo" accept="image/*">
        </div>

        <!-- Email address -->
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="jdgomezcubillo2004@gmail.com" required>
        </div>

        <!-- Password and repeat password -->
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

        <!-- Address -->
        <div class="field">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="Maracaná, Ciudad Quesada" required>
        </div>

        <!-- Country and state -->
        <div class="row">
            <div class="field">
                <label for="country">Country</label>
                <select id="country" name="country" required>
                    <option value="" disabled selected>Select Country</option>
                    <option value="CR">Costa Rica</option>
                    <option value="MX">México</option>
                </select>
            </div>
            <div class="field">
                <label for="state">State</label>
                <input type="text" id="state" name="state" value="Alajuela" required>
            </div>
        </div>

        <!-- City and phone number -->
        <div class="row">
            <div class="field">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="Quesada" required>
            </div>
            <div class="field">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="+506 70165004" required>
            </div>
        </div>

        <!-- Form action buttons -->
        <div class="form-actions">
            <a class="cancel-link" onclick="window.history.back()">Cancel</a>
            <button type="submit" class="submit-btn" onclick="window.history.back()">Save</button>
        </div>
    </form>

    <!-- Footer with navigation links -->
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

    <script src="Scripts/home/profile.js"></script>

</body>


</html>