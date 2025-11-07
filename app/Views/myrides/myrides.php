<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rides - AVENTONES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/rides.css">

</head>

<body>
    <!-- Header with logo and title -->
    <div class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </div>

    <!-- Navigation menu with search and user profile -->
    <div class="menu-container">
        <div class="menu">
            <!-- Left navigation links -->
            <nav class="left-menu">
                <a href="../rides/searchrides.php">Home</a>
                <a class="active" href="../myrides/myrides.php">Rides</a>
                <a href="../bookings/bookings.php">Bookings</a>
            </nav>

            <!-- Centered search input -->
            <div class="center-search">
                <input type="text" placeholder="Search..." class="search-bar">
            </div>

            <!-- Right side: user icon with dropdown menu -->
            <div class="right-menu">
                <div class="user-btn">
                    <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
                    <div class="dropdown-menu">
                        <a href="index.php">Logout</a>
                        <a href="../profile/editprofile.php">Profile</a>
                        <a href="../profile/configuration.php">Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!-- Page title and "New Ride" button -->
    <h2 class="subtitle">My rides</h2>
    <div class="button-container">
        <a href="newride.php" class="new-ride">New Ride</a>
    </div>

    <!-- Rides table -->
    <table>
        <thead>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>Seats</th>
                <th>Car</th>
                <th>Fee</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- 
            Ride 1
            <tr>
                <td><a href="detailsride.html">Quesada</a></td>
                <td>Alajuela</td>
                <td>2</td>
                <td>Ford Mustang EcoBoost 2021</td>
                <td>$15</td>
                <td class="actions">
                    <a href="editride.html">Edit</a>
                    <a href="#" onclick="this.closest('tr').style.display='none'; return false;">Delete</a>
                </td>
            </tr>

            Ride 2
            <tr>
                <td><a href="detailsride.html">Quesada</a></td>
                <td>Naranjo</td>
                <td>1</td>
                <td>Mazda MX-5 Miata 2020</td>
                <td>$10</td>
                <td class="actions">
                    <a href="editride.html">Edit</a>
                    <a href="#" onclick="this.closest('tr').style.display='none'; return false;">Delete</a>
                </td>
            </tr>

            Ride 3 
            <tr>
                <td><a href="detailsride.html">Aguas Zarcas</a></td>
                <td>Naranjo</td>
                <td>1</td>
                <td>Hyundai Veloster Turbo 2022</td>
                <td>$20</td>
                <td class="actions">
                    <a href="editride.html">Edit</a>
                    <a href="#" onclick="this.closest('tr').style.display='none'; return false;">Delete</a>
                </td>
            </tr> 
            -->
        </tbody>
    </table>

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

    <script src="./Scripts/myrides/verify-type.js"></script>
    <script src="./Scripts/myrides/myrides.js"></script>


</body>

</html>