<?php
// Include the database connection file
include '../../Backend_Implementation/partials/db_connect.php';

// SQL query to fetch movie details along with associated cinema information
$sql = "SELECT m.title, m.genres, m.actors, c.name as cinema
        FROM movies m
        LEFT JOIN cinema_movies cm ON m.id = cm.movie_id
        LEFT JOIN cinemas c ON cm.cinema_id = c.id";

$result = $conn->query($sql);

// Initialize an array to store the movie details
$movies = [];
if ($result->num_rows > 0) {
    // Fetch each row and add it to the movies array
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}

// Close the database connection
$conn->close();

// Output the movies array as a JSON object
echo json_encode($movies);
?>
