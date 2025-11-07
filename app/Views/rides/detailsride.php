<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Details - AVENTONES</title>
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
            <!-- Left navigation links -->
            <nav class="left-menu">
                <a href="../rides/searchrides.php">Home</a>
                <a class="active" href="../myrides/myrides.php">Rides</a>
                <a href="../bookings/bookings.php">Bookings</a>
            </nav>

            <!-- Center search bar -->
            <div class="center-search">
                <input type="text" placeholder="Search..." class="search-bar">
            </div>

            <!-- Right-side user dropdown -->
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
    <h2>Ride Details</h2>

    <!-- Profile information -->
    <div class="profile-section">
        <img src="Images/avatar.png" alt="Profile Picture" class="profile-pic">
        <p>juanda_gomez04</p>
    </div>

    <!-- Ride detail form (read-only style) -->
    <form class="ride-form" action="../myrides/myrides.php" method="get">

        <!-- Departure and arrival information -->
        <div class="row">
            <div class="field">
                <label for="departure">Departure from</label>
                <input type="text" id="departure" value="Quesada" required>
            </div>
            <div class="field">
                <label for="arrival">Arrive To</label>
                <input type="text" id="arrival" value="Zarcero" required>
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

        <!-- Vehicle information section -->
        <fieldset class="vehicle-details">
            <legend>Vehicle Details</legend>
            <div class="row">
                <div class="field">
                    <label for="make">Make</label>
                    <select id="make">
                        <option selected>Nissan</option>
                    </select>
                </div>
                <div class="field">
                    <label for="model">Model</label>
                    <input type="text" id="model" value="March">
                </div>
                <div class="field">
                    <label for="year">Year</label>
                    <input type="number" id="year" value="2020">
                </div>

            </div>
            <div class="field photo-field" style="flex:0 0 auto; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                <label for="photo" style="margin-bottom:8px;">Photo</label>
                <div id="photoPreview" style="
                    position: relative;
                    width: 200px;
                    height: 200px;
                    border: 2px dashed #ccc;
                    border-radius: 8px;
                    overflow: hidden;
                    background: #f9f9f9;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <img id="photoImage" src="../../../public/assets/img/Icono.png" alt="Photo preview" style="
                        width: 200px;
                        height: 200px;
                        object-fit: cover;
                        display: block;
                        max-width: none;
                        border-radius: 8px;
                    ">
                </div>
            </div>

        </fieldset>

        <!-- Action buttons -->
        <div class="buttons">
            <button type="button" class="cancel-btn" onclick="window.history.back()">Back</button>
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

    <script src="./Scripts/myrides/detailsride.js"></script>

</body>

</html>