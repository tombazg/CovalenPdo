<?php
// Start a session or resume the existing session
session_start();

// Include the database connection file
include 'Backend_Implementation/partials/db_connect.php';

// Get movie ID from URL
$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch movie details from the database
$sql = "SELECT * FROM movies WHERE id = $movie_id";
$result = mysqli_query($conn, $sql);
$movie = mysqli_fetch_assoc($result);

// Check if the movie exists
if (!$movie) {
    echo "Movie not found!";
    exit;
}

// Function to fetch showtimes from the database
function fetchShowtimes($conn, $movie_id) {
    $sql_showtimes = "SELECT showtimes FROM cinema_movies WHERE movie_id = $movie_id";
    $result_showtimes = mysqli_query($conn, $sql_showtimes);
    $showtimes = [];
    if ($result_showtimes) {
        while ($row = mysqli_fetch_assoc($result_showtimes)) {
            $showtimes = array_merge($showtimes, explode(',', $row['showtimes']));
        }
    }
    return $showtimes;
}

// Fetch showtimes for the movie
$showtimes = fetchShowtimes($conn, $movie_id);

// Add to watchlist logic
if (isset($_POST['add_to_watchlist'])) {
    $user_id = $_SESSION['user_id'];
    $sql_add_to_watchlist = "INSERT INTO watchlist (user_id, movie_id) VALUES ('$user_id', '$movie_id')";

    // Check if the movie was added to the watchlist successfully
    if (mysqli_query($conn, $sql_add_to_watchlist)) {
        $_SESSION['message'] = "Movie added to your watchlist!";
    } else {
        $_SESSION['message'] = "Failed to add movie to watchlist.";
    }

    // Redirect to the movie description page
    header("Location: movieDescription.php?id=$movie_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <title><?php echo htmlspecialchars($movie['title']); ?> - CineSphere</title>
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .navbar-brand, .nav-link {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }
        .container {
            margin-top: 80px;
        }
        .movie-details {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }
        .movie-title {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .movie-meta {
            font-size: 1.2em;
            margin-bottom: 20px;
            color: #fff;
        }
        .movie-meta .dot::before {
            content: "•";
            margin: 0 5px;
            color: #fff;
        }
        .imdb-rating {
            color: gold;
            font-weight: bold;
            font-size: 1.2em;
        }
        .movie-content {
            display: flex;
            justify-content: space-between;
            gap: 0 px;
        }
        .movie-photo {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-right: -520px;
        }
        .movie-trailer {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .movie-photo img {
            width: 35%;
            border-radius: 10px;
        }
        .movie-trailer iframe {
            width: 100%;
            height: 100%;
            border-radius: 10px;
        }
        .genre {
            display: flex;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        .genre span {
            color: #fff;
            padding: 5px 10px;
            border: 1px solid #fff;
            border-radius: 5px;
            margin: 5px;
        }
        .white-line {
            border-top: 1px solid #fff;
            margin: 20px 0;
        }
        .actors-section {
            font-size: 1.1em;
            color: #fff;
            display: flex;
            align-items: center;
        }
        .actors-section h4 {
            font-weight: bold;
            margin-right: 10px;
        }
        .actors {
            font-size: 1.1em;
        }
        .actors a {
            color: dodgerblue;
            text-decoration: none;
        }
        .actors a::after {
            content: ' • ';
            color: white;
        }
        .actors a:last-child::after {
            content: '';
        }
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .book-button, .add-list-button {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .book-button {
            background-color: yellow;
            color: #444;
        }
        .book-button:hover {
            background-color: #ffd700;
        }
        .add-list-button {
            background-color: green;
            color: #fff;
        }
        .add-list-button:hover {
            background-color: #32cd32;
        }
        .back-button {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            margin-left: 10px;
        }
        .back-button:hover {
            background-color: #ff4c4c;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">CineSphere</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="Backend_Implementation/api/auth/mainPage.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Backend_Implementation/api/booking/bookTicket.php">Booking</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="watchlist.php">Watchlist</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Backend_Implementation/api/auth/logout.php">Logout</a>
                </li>
            </ul>
            <a class="back-button" href="userMainPage.php">Back</a>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <div class="movie-details">
            <div class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></div>
            <div class="movie-meta">
                <?php echo htmlspecialchars($movie['release_date']); ?> 
                <span class="dot"></span>
                <?php echo htmlspecialchars($movie['duration']); ?> min
            </div>
            <div class="imdb-rating">IMDB: <?php echo htmlspecialchars($movie['rating']); ?></div>
        </div>
        <div class="movie-content">
            <div class="movie-photo">
                <img src="<?php echo htmlspecialchars($movie['image_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
            </div>
            <div class="movie-trailer">
                <?php echo $movie['trailers']; ?>
            </div>
        </div>
        <div class="genre">
            <?php 
            $genres = explode(',', $movie['genres']);
            foreach ($genres as $genre): ?>
                <span><?php echo htmlspecialchars(trim($genre)); ?></span>
            <?php endforeach; ?>
        </div>
        <div class="description"><?php echo htmlspecialchars($movie['description']); ?></div>
        <div class="white-line"></div>
        <div class="actors-section">
            <h4>Actors</h4>
            <div class="actors">
                <?php 
                $actors = explode(',', $movie['actors']);
                foreach ($actors as $actor): ?>
                    <a href="#"><?php echo htmlspecialchars(trim($actor)); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="action-buttons">
            <a href="Backend_Implementation/api/booking/bookTicket.php" class="book-button">Book</a>
            <form method="post" style="display:inline;">
                <button type="submit" name="add_to_watchlist" class="add-list-button">Add to List</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>
