<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Rides - AVENTONES</title>
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

    <!-- Top navigation menu with left links, center search, and right user menu -->
    <div class="menu-container">
        <div class="menu">

            <!-- Left navigation links -->
            <nav class="left-menu">
                <a class="active" href="../rides/searchrides.php">Home</a>
                <a  href="../myrides/myrides.php">Rides</a>
                <a href="../bookings/bookings.php">Bookings</a>
            </nav>

            <!-- Centered search input -->
            <div class="center-search">
                <input type="text" placeholder="Search..." class="search-bar">
            </div>

            <!-- Right-side user icon with dropdown options -->
            <div class="right-menu">
                <div class="user-btn">
                    <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
                    <div class="dropdown-menu">
                        <a href="../auth/login.php">Logout</a>
                        <a href="../profile/editprofile.php">Profile</a>
                        <a href="../profile/configuration.php">Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!-- Page heading -->
    <h2>Search Rides</h2>

    <!-- Filter form for searching rides -->
    <div class="filter-box">
        <form action="#" method="get" class="filter-form">

            <!-- Origin, destination, and submit button -->
            <div class="filter-row">
                <label for="from">From</label>
                <select id="from" name="from">
                    <option value="">All</option>
                </select>

                <label for="to">To</label>
                <select id="to" name="to">
                    <option value="">All</option>
                </select>


                <button type="submit" class="find-btn">Find rides</button>
            </div>

            <!-- Days of the week checkboxes -->
            <div class="filter-days">
                <label>Days</label>
                <label><input type="checkbox" name="days" > Mon</label>
                <label><input type="checkbox" name="days" > Tue</label>
                <label><input type="checkbox" name="days" > Wed</label>
                <label><input type="checkbox" name="days" > Thu</label>
                <label><input type="checkbox" name="days"> Fri</label>
                <label><input type="checkbox" name="days" > Sat</label>
                <label><input type="checkbox" name="days"> Sun</label>
            </div>
        </form>
    </div>

    <!-- Search results table -->
    <div class="results">
        <p>Rides found from <strong><em>...</em></strong> to <strong><em>...</em></strong></p>

        <table class="rides-table">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Seats</th>
                    <th>Car</th>
                    <th>Fee</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="ridesBody"><!-- dinámico --></tbody>
        </table>

        </table>
    </div>

    <!-- Contenedor del mapa -->
    <div id="map"></div>


    <!-- Footer with navigation links -->
    <footer>
        <hr>
        <nav>
            <a href="../rides/searchrides.php">Home</a> |
            <a href="../myrides/myrides.php">Rides</a> |
            <a href="../bookings/bookings.php">Bookings</a> |
            <a href="../profile/configuration.php">Settings</a> |
            <a href="index.php">Login</a> |
            <a href="../auth/register.php">Register</a>
        </nav>
        <p>&copy; Aventones.com</p>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="./Scripts/home/apimaps.js"></script>
    <script src="./Scripts/home/searchrides.js"></script>
</body>

</html>