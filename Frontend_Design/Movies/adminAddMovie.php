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
    include '../../Backend_Implementation/partials/db_connect.php';

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
    $image_url = $_POST["image_url"];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO movies (title, description, category, release_date, rating, price, genres, actors, duration, trailers, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdsssss", $title, $description, $category, $release_date, $rating, $price, $genres, $actors, $duration, $trailers, $image_url);

    // Execute the prepared statement and set the message based on the result
    if ($stmt->execute()) {
        $message = "Movie added successfully.";
    } else {
        $message = "Error adding movie: " . $stmt->error;
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Add Movie</title>
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
        .form-group textarea,
        .form-group select {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            color: black;
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
        .form-group .tooltip {
            margin-left: 5px;
            color: red;
        }
        .form-group input::placeholder, 
        .form-group textarea::placeholder {
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
            <a href="adminMoviesDashboard.php" class="back-button">Back</a>
            <a href="../../adminDashboard.php" class="home-button">Home</a>
        </div>
        <h1>Add New Movie</h1>
        <?php if ($message): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($message) ?>
                <button onclick="window.location.href='adminMoviesDashboard.php'">OK</button>
            </div>
        <?php endif; ?>
        <form id="addMovieForm" action="adminAddMovie.php" method="post">
            <div class="form-group">
                <label for="title">Movie Title</label>
                <input type="text" id="title" name="title" placeholder="Movie Title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Description of Movie" required></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="image_url">Image URL <span class="tooltip" title="Add image URL Path. Add image inside Frontend_Design folder and inside images folder, image has to be jpg.">!</span></label>
                    <input type="text" id="image_url" name="image_url" placeholder="Add image URL Path" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Adventure">Adventure</option>
                        <option value="Drama">Drama</option>
                        <option value="Horror">Horror</option>
                        <option value="Fantasy">Fantasy</option>
                        <option value="Animation">Animation</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="release_date">Release Year</label>
                    <input type="number" id="release_date" name="release_date" min="1900" max="2100" placeholder="Release Year" required>
                </div>
                <div class="form-group">
                    <label for="rating">Rating</label>
                    <input type="number" step="0.1" id="rating" name="rating" placeholder="Rating" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>
                </div>
                <div class="form-group">
                    <label for="genres">Genres</label>
                    <input type="text" id="genres" name="genres" placeholder="Genre" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="actors">Actors</label>
                    <input type="text" id="actors" name="actors" placeholder="Actors" required>
                </div>
                <div class="form-group">
                    <label for="duration">Duration (in minutes)</label>
                    <input type="number" step="1" id="duration" name="duration" placeholder="Duration" required>
                </div>
            </div>
            <div class="form-group">
                <label for="trailers">Trailers <span class="tooltip" title="Add Trailer in form of HTML iframe tag">!</span></label>
                <textarea id="trailers" name="trailers" placeholder="Add Trailer in form of HTML iframe tag" required></textarea>
            </div>
            <button type="submit" class="btn">Add Movie</button>
        </form>
    </div>
</body>
</html>
