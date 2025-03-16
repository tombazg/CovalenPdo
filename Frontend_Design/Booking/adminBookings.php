<?php
// Start a session or resume the existing session
session_start();

// Check if the user is logged in, is an admin, and redirect to login page if not
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true || !$_SESSION['is_admin']) {
    header("location: ../auth/login.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Bookings Dashboard</title>
    <style>
        body {
            background-color: white;
            color: black;
            font-family: 'DM Sans', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 1200px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 1em;
            margin-right: 10px;
        }
        .back-button {
            background-color: red;
        }
        .home-button {
            background-color: yellow;
            color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Booking List</h1>
        <div class="top-buttons">
            <a href="adminBookingDashboard.php" class="btn back-button">Back</a>
            <a href="../../adminDashboard.php" class="btn home-button">Home</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Movie</th>
                    <th>Seat Number</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="bookingsTableBody">
                <!-- Table rows will be inserted here by JavaScript -->
            </tbody>
        </table>
    </div>

    <script>
        function fetchBookings() {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "fetchBookings.php", true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const bookings = JSON.parse(this.responseText);
                    let output = '';
                    bookings.forEach(function (booking) {
                        output += `
                            <tr>
                                <td>${booking.username}</td>
                                <td>${booking.title}</td>
                                <td>${booking.seat_number}</td>
                                <td>${booking.booking_date}</td>
                                <td>${booking.status}</td>
                            </tr>
                        `;
                    });
                    document.getElementById('bookingsTableBody').innerHTML = output;
                }
            }
            xhr.send();
        }

        // Fetch bookings every 5 seconds
        setInterval(fetchBookings, 5000);

        // Initial fetch
        fetchBookings();
    </script>
</body>
</html>
