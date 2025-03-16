<?php
// Start a session or resume the existing session
session_start();

// Check if the user is logged in by verifying if 'user_id' exists in the session
$is_logged_in = isset($_SESSION['user_id']);

// Check if the logged-in user is an admin by verifying 'is_admin' in the session
$is_admin = $is_logged_in && isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;

// If the user is not logged in or not an admin, redirect to the admin dashboard
if (!$is_logged_in || !$is_admin) {
    header("Location: ../../adminDashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Dashboard - CineSphere</title>
    <style>
        body {
            background-color: black;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'DM Sans', sans-serif;
        }

        h1 {
            font-size: 5em;
            margin-bottom: 1em;
        }

        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1em;
            margin-bottom: 12em;
        }

        .button-row {
            display: flex;
            gap: 1em;
        }

        .button-container a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 200px;
            height: 200px;
            font-size: 2em;
            font-weight: bold;
            color: #ccc;
            text-decoration: none;
            border-radius: 10px;
            transition: transform 0.3s;
        }

        .button-container a:hover {
            transform: scale(1.1);
        }

        .button-green {
            background-color: green;
        }

        .button-purple {
            background-color: purple;
        }

        .button-yellow {
            background-color: yellow;
        }

        .button-red {
            background-color: red;
        }

        .button-blue {
            background-color: blue;
        }

        .button-pink {
            background-color: #ff69b4;
        }
    </style>
</head>
<body>
    <h1>Bookings Dashboard</h1>
    <div class="button-container">
        <div class="button-row">
            <a href="adminEditBookings.php" class="button button-yellow">Edit Booking</a>
            <a href="adminBookings.php" class="button button-blue">Booking List</a>
        </div>
        <div class="button-row">
            <a href="../../adminDashboard.php" class="button button-pink">Home</a>
            <a href="../../Backend_Implementation/api/auth/logout.php" class="button button-red">Logout</a>
        </div>
    </div>
</body>
</html>
