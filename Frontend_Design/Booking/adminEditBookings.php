<?php
// Start a session or resume the existing session
session_start();

// Check if the user is logged in, is an admin, and redirect to login page if not
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true || !$_SESSION['is_admin']) {
    header("location: ../auth/login.php");
    exit;
}

// Initialize a variable to store messages
$message = '';

// Include the database connection file
include '../../Backend_Implementation/partials/db_connect.php';

// Fetch all bookings for the dropdown
$bookings = [];
$result = $conn->query("SELECT b.id, u.username, m.title 
                        FROM bookings b
                        JOIN users u ON b.user_id = u.id
                        JOIN movies m ON b.movie_id = m.id");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Fetch all movies for the dropdown
$movies = [];
$result = $conn->query("SELECT id, title FROM movies");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}

// Fetch all users for the dropdown
$users = [];
$result = $conn->query("SELECT id, username FROM users");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// If a booking is selected, fetch its details
$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : null;
$booking = null;
if ($booking_id) {
    $stmt = $conn->prepare("SELECT b.*, u.username, m.title 
                            FROM bookings b
                            JOIN users u ON b.user_id = u.id
                            JOIN movies m ON b.movie_id = m.id
                            WHERE b.id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    }
    $stmt->close();
}

// Handle form submission for updating a booking
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST["booking_id"];
    $movie_id = $_POST["movie_id"];
    $user_id = $_POST["user_id"];
    $booking_date = $_POST["booking_date"];
    $status = $_POST["status"];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("UPDATE bookings SET movie_id = ?, user_id = ?, booking_date = ?, status = ? WHERE id = ?");
    $stmt->bind_param("iissi", $movie_id, $user_id, $booking_date, $status, $booking_id);

    // Execute the prepared statement and set the message based on the result
    if ($stmt->execute()) {
        $message = "Booking updated successfully.";
    } else {
        $message = "Error updating booking: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Booking</title>
    <style>
        body {
            background-color: white;
            color: black;
            font-family: 'DM Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        .container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, 
        .form-group select {
            width: calc(100% - 20px); 
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            color: black;
        }
        .form-group input[type="date"] {
            width: calc(100% - 20px);
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .alert {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            color: white;
            background-color: #ff4c4c;
            text-align: center;
        }
        .alert-info {
            background-color: #5cb85c;
        }
        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .form-row .form-group {
            width: calc(50% - 10px);
        }
        .form-group input::placeholder, 
        .form-group select::placeholder {
            color: lightgrey;
        }
        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .top-buttons a {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 1em;
            position: relative;
        }
        .back-button {
            background-color: red;
        }
        .back-button::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 2px;
            background-color: #cc0000;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        .back-button:hover::after {
            transform: scaleX(1.5);
        }
        .home-button {
            background-color: yellow;
            color: lightgrey !important;
        }
        .home-button::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 2px;
            background-color: #cccc00;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        .home-button:hover::after {
            transform: scaleX(1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-buttons">
            <a href="adminBookingDashboard.php" class="back-button">Back</a>
            <a href="../../adminDashboard.php" class="home-button">Home</a>
        </div>
        <h1>Edit Booking</h1>
        <?php if ($message): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($message) ?>
                <button onclick="window.location.href='adminBookingDashboard.php'">OK</button>
            </div>
        <?php endif; ?>
        <form id="selectBookingForm" method="get" action="adminEditBookings.php">
            <div class="form-group">
                <label for="booking_id">Select Booking to Edit</label>
                <select id="booking_id" name="booking_id" onchange="populateBookingDetails()" required>
                    <option value="">Select a booking</option>
                    <?php foreach ($bookings as $b): ?>
                        <option value="<?= htmlspecialchars($b['id']) ?>" <?= $booking_id == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['username']) ?> - <?= htmlspecialchars($b['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <?php if ($booking): ?>
        <form id="editBookingForm" action="adminEditBookings.php?booking_id=<?= $booking_id ?>" method="post">
            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
            <div class="form-group">
                <label for="movie_id">Movie Title</label>
                <select id="movie_id" name="movie_id" required>
                    <?php foreach ($movies as $m): ?>
                        <option value="<?= htmlspecialchars($m['id']) ?>" <?= $booking['movie_id'] == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="user_id">Username</label>
                <select id="user_id" name="user_id" required>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= htmlspecialchars($u['id']) ?>" <?= $booking['user_id'] == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="booking_date">Booking Date</label>
                <input type="date" id="booking_date" name="booking_date" placeholder="Booking Date" value="<?= htmlspecialchars($booking['booking_date']) ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="confirmed" <?= $booking['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="canceled" <?= $booking['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                </select>
            </div>
            <button type="submit" class="btn">Update Booking</button>
        </form>
        <?php endif; ?>
    </div>
    <script>
        function populateBookingDetails() {
            const bookingId = document.getElementById('booking_id').value;
            if (bookingId) {
                window.location.href = `adminEditBookings.php?booking_id=${bookingId}`;
            }
        }
    </script>
</body>
</html>
