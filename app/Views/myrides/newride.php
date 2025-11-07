<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Ride - AVENTONES</title>
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

    <!-- Top navigation menu -->
    <div class="menu-container">
        <div class="menu">
            <!-- Left side navigation -->
            <nav class="left-menu">
                <a href="../rides/searchrides.php">Home</a>
                <a class="active" href="#">Rides</a>
                <a href="../bookings/bookings.php">Bookings</a>
            </nav>

            <!-- Centered search bar -->
            <div class="center-search">
                <input type="text" placeholder="Search..." class="search-bar">
            </div>

            <!-- Right side user dropdown -->
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

    <!-- Page subtitle -->
    <h2 class="subtitle">New Ride</h2>

    <!-- Ride creation form -->
    <form class="ride-form">

        <!-- Departure and arrival fields -->
        <div class="row">
            <div class="field">
                <label for="departure">Departure from</label>
                <input type="text" id="departure" placeholder="Enter departure location" required>
            </div>
            <div class="field">
                <label for="arrival">Arrive to</label>
                <input type="text" id="arrival" placeholder="Enter arrival location" required>
            </div>
        </div>

        <!-- Days (su propia fila) -->
        <div class="row">
            <div class="field days">
                <label>Days</label>
                <div class="days-checkboxes">
                    <label><input type="checkbox" name="days" value="Mon"> Mon</label>
                    <label><input type="checkbox" name="days" value="Tue"> Tue</label>
                    <label><input type="checkbox" name="days" value="Wed"> Wed</label>
                    <label><input type="checkbox" name="days" value="Thu"> Thu</label>
                    <label><input type="checkbox" name="days" value="Fri"> Fri</label>
                    <label><input type="checkbox" name="days" value="Sat"> Sat</label>
                    <label><input type="checkbox" name="days" value="Sun"> Sun</label>
                </div>
            </div>
        </div>

        <!-- Time, Seats, Fee (siguiente fila separada) -->
        <div class="row">
            <div class="details_field">
                <label for="time">Time</label>
                <select id="time">
                    <option>08:00 am</option>
                    <option>09:00 am</option>
                    <option>10:00 am</option>
                    <option>11:00 am</option>
                    <option>12:00 pm</option>
                    <option>01:00 pm</option>
                    <option>02:00 pm</option>
                    <option>03:00 pm</option>
                    <option>04:00 pm</option>
                </select>
            </div>
            <div class="details_field">
                <label for="seats">Seats</label>
                <input type="number" id="seats" min="1" max="10" value="1">
            </div>
            <div class="details_field">
                <label for="fee">Fee</label>
                <input type="number" id="fee" min="0" value="0">
            </div>
        </div>

        <!-- Vehicle details section -->
        <fieldset class="vehicle-details">
            <legend>Vehicle Details</legend>
            <div class="row">
                <div class="field">
                    <label for="make">Make</label>
                    <select id="make">
                        <option value="Nissan">Nissan</option>
                        <option value="Toyota">Toyota</option>
                        <option value="Honda">Honda</option>
                        <option value="Ford">Ford</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="field">
                    <label for="model">Model</label>
                    <input type="text" id="model" placeholder="Car Model">
                </div>
                <div class="field">
                    <label for="year">Year</label>
                    <input type="number" id="year" min="1990" max="2025" value="2020">
                </div>
                <div class="field">
                    <label for="photo">Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                </div>
            </div>

        </fieldset>

        <!-- Form action buttons -->
        <div class="buttons">
            <a href="myrides.php" class="cancel-btn">Cancel</a>
            <button type="submit" class="next-btn">Create</button>
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
            <a href="index.php">Login</a> |
            <a href="../auth/register.php">Register</a>
        </nav>
        <p>&copy; Aventones.com</p>
    </footer>

    <!-- al final de newride.html, antes de /body -->
    <script src="./Scripts/myrides/newride.js"></script>

</body>

</html>