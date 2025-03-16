<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the content type to JSON for the response
header('Content-Type: application/json');

// Include the database connection file
include 'Backend_Implementation/partials/db_connect.php';

// Get the search query from the URL parameter
$query = isset($_GET['q']) ? $_GET['q'] : '';

// Check if the query is not empty
if (!empty($query)) {
    // Escape special characters in the query for use in the SQL statement
    $query = mysqli_real_escape_string($conn, $query);

    // Search for movies by title or actors
    $sql = "SELECT id, title AS name, 'movie' AS type FROM movies WHERE title LIKE '%$query%'
            UNION
            SELECT id, actors AS name, 'actor' AS type FROM movies WHERE actors LIKE '%$query%'";
    
    $result = mysqli_query($conn, $sql);

    // Check if the query was successful
    if ($result) {
        $searchResults = [];
        // Fetch each row and add it to the search results array
        while ($row = mysqli_fetch_assoc($result)) {
            $searchResults[] = $row;
        }
        // Output the search results as a JSON object
        echo json_encode($searchResults);
    } else {
        // Output an error message if the query failed
        echo json_encode(['error' => 'Database query failed']);
    }
} else {
    // Output an error message if no search query was provided
    echo json_encode(['error' => 'No search query provided']);
}
?>