<?php
// Include the database connection file
include '../../Backend_Implementation/partials/db_connect.php';

// SQL query to fetch booking details along with user, movie, and seat information
$sql = "SELECT b.id, u.username, m.title, bs.seat_number, b.booking_date, b.status 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN movies m ON b.movie_id = m.id
        LEFT JOIN booking_seats bs ON b.id = bs.booking_id";

$result = $conn->query($sql);

// Initialize an array to store the booking details
$bookings = [];
if ($result->num_rows > 0) {
    // Fetch each row and add it to the bookings array
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Close the database connection
$conn->close();

// Output the bookings array as a JSON object
echo json_encode($bookings);
?>