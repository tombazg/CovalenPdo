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

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include the database connection file
    include '../../partials/db_connect.php';

    // Get form data
    $title = $_POST["title"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $release_date = $_POST["release_date"];
    $rating = $_POST["rating"];
    $price = $_POST["price"];
    $genres = $_POST["genres"];
    $actors = $_POST["actors"];
    $duration = $_POST["duration"];
    $trailers = $_POST["trailers"];

    // Handle file upload
    $target_dir = "../../Frontend_Design/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Set the image URL for the uploaded image
        $image_url = "images/" . basename($_FILES["image"]["name"]);

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO movies (title, description, image_url, category, release_date, rating, price, genres, actors, duration, trailers, seen, predelete) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $seen = 0;
        $predelete = 0;
        $stmt->bind_param("sssssddsddsii", $title, $description, $image_url, $category, $release_date, $rating, $price, $genres, $actors, $duration, $trailers, $seen, $predelete);

        // Execute the prepared statement and set the message based on the result
        if ($stmt->execute()) {
            $message = "Movie added successfully.";
        } else {
            $message = "Error adding movie: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Set message if there is an error uploading the image
        $message = "Error uploading image.";
    }

    // Close the database connection
    $conn->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <title>Add Movie</title>
</head>
<body>
    <?php require '../../partials/nav.php'; ?>
    <div class="container my-4">
        <h1 class="text-center">Add New Movie</h1>
        <?php if ($message): ?>
            <div class="alert alert-info" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form id="addMovieForm" action="add_movie.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Movie Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="image">Movie Image</label>
                <input type="file" class="form-control-file" id="image" name="image" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" class="form-control" id="category" name="category" required>
            </div>
            <div class="form-group">
                <label for="release_date">Release Date</label>
                <input type="date" class="form-control" id="release_date" name="release_date" required>
            </div>
            <div class="form-group">
                <label for="rating">Rating</label>
                <input type="number" step="0.1" class="form-control" id="rating" name="rating" required>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="genres">Genres</label>
                <input type="text" class="form-control" id="genres" name="genres" required>
            </div>
            <div class="form-group">
                <label for="actors">Actors</label>
                <input type="text" class="form-control" id="actors" name="actors" required>
            </div>
            <div class="form-group">
                <label for="duration">Duration (hours)</label>
                <input type="number" step="0.1" class="form-control" id="duration" name="duration" required>
            </div>
            <div class="form-group">
                <label for="trailers">Trailers</label>
                <textarea class="form-control" id="trailers" name="trailers" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Movie</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="../../Frontend_Design/js/validation.js"></script>
</body>
</html>
