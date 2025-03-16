<?php
// Include the database connection file
include '../../Backend_Implementation/partials/db_connect.php';

// SQL query to fetch user details along with their watchlist movies
$sql = "SELECT u.username, u.password, u.email, u.date_of_birth, u.phone_number, GROUP_CONCAT(m.title SEPARATOR ', ') as movies
        FROM users u
        LEFT JOIN watchlist w ON u.id = w.user_id
        LEFT JOIN movies m ON w.movie_id = m.id
        GROUP BY u.id";

$result = $conn->query($sql);

// Initialize an array to store the user details
$users = [];
if ($result->num_rows > 0) {
    // Fetch each row and add it to the users array
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'username' => $row['username'],
            'password' => $row['password'],
            'email' => $row['email'],
            'date_of_birth' => $row['date_of_birth'],
            'phone_number' => $row['phone_number'],
            'movies' => $row['movies'] ? explode(', ', $row['movies']) : []
        ];
    }
}

// Close the database connection
$conn->close();

// Output the users array as a JSON object
echo json_encode($users);
?>