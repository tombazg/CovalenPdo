<?php
// Start a session or resume the existing session
session_start();

// Include the database connection file
include 'Backend_Implementation/partials/db_connect.php';

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: Backend_Implementation/api/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch movies in the user's watchlist with optional filters
function fetchMovies($conn, $user_id, $actor = null, $movie_id = null) {
    $sql = "SELECT m.* FROM watchlist w JOIN movies m ON w.movie_id = m.id WHERE w.user_id = $user_id";

    if ($actor !== null && $actor !== 'all') {
        $sql .= " AND FIND_IN_SET('$actor', m.actors)";
    }

    if ($movie_id !== null && $movie_id !== 'all') {
        $sql .= " AND m.id = $movie_id";
    }
    
    $result = mysqli_query($conn, $sql);
    $movies = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $movies[] = $row;
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    
    return $movies;
}

$actor = isset($_GET['actor']) ? $_GET['actor'] : null;
$movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : null;

$watchlist_movies = fetchMovies($conn, $user_id, $actor, $movie_id);

// Fetch all actors from the movies
$all_actors = [];
foreach ($watchlist_movies as $movie) {
    $movie_actors = explode(',', $movie['actors']);
    foreach ($movie_actors as $actor) {
        if (!in_array(trim($actor), $all_actors)) {
            $all_actors[] = trim($actor);
        }
    }
}

// Fetch all movies from the user's watchlist
$all_movies = [];
foreach ($watchlist_movies as $movie) {
    if (!in_array($movie, $all_movies)) {
        $all_movies[] = $movie;
    }
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

// Handle delete movie from watchlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_movie'])) {
    $movie_id_to_delete = $_POST['movie_id'];
    $sql_delete = "DELETE FROM watchlist WHERE user_id = $user_id AND movie_id = $movie_id_to_delete";
    if (mysqli_query($conn, $sql_delete)) {
        header("Location: watchlist.php");
        exit;
    } else {
        echo "Error deleting movie from watchlist: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <title>Watchlist - CineSphere</title>
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .navbar-brand {
            color: #fff;
            font-size: 24px;
            font-weight: bold;
        }
        .nav-link {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }
        .nav-link:hover {
            color: blue;
        }
        .container {
            margin-top: 80px;
        }
        .movie-card {
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            position: relative;
            height: 250px;
        }
        .movie-card img {
            width: 150px;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }
        .movie-info {
            padding: 10px;
            flex: 1;
        }
        .movie-info a {
            color: #fff;
            text-decoration: none;
        }
        .movie-info a:hover {
            color: blue;
        }
        .imdb-rating {
            color: gold;
            font-weight: bold;
        }
        .showtime {
            color: #fff;
            background-color: red;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin: 2px;
        }
        .book-button {
            position: absolute;
            bottom: 20px;
            right: 100px;
            background-color: yellow;
            color: #444!important;
            padding: 5.3px 13px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
        .book-button:hover {
            background-color: #CC5500;
        }
        .delete-button {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background-color: orange;
            color: #fff;
            padding: 4.5px 10px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: #FF4500;
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
        .movie-row-divider {
            border-top: 1px solid #fff;
            margin-top: 20px;
            padding-top: 20px;
        }
    </style>
    <script>
        function fetchFilteredMovies() {
            const actor = document.getElementById('actorSelect').value;
            const movieId = document.getElementById('movieSelect').value;

            let queryString = '';

            if (actor && actor !== 'all') {
                queryString += `actor=${actor}&`;
            }

            if (movieId && movieId !== 'all') {
                queryString += `movie_id=${movieId}&`;
            }

            window.location.href = `watchlist.php?${queryString}`;
        }

        function populateActors(actors) {
            const actorSelect = document.getElementById('actorSelect');
            actorSelect.innerHTML = '<option value="">Choose an Actor</option><option value="all">All Actors</option>';
            actors.forEach(actor => {
                actorSelect.innerHTML += `<option value="${actor}">${actor}</option>`;
            });
        }

        function populateMovies(movies) {
            const movieSelect = document.getElementById('movieSelect');
            movieSelect.innerHTML = '<option value="">Choose a Film</option><option value="all">All Movies</option>';
            movies.forEach(movie => {
                movieSelect.innerHTML += `<option value="${movie.id}">${movie.title}</option>`;
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const allActors = <?php echo json_encode($all_actors); ?>;
            const allMovies = <?php echo json_encode(array_map(function($movie) { return ['id' => $movie['id'], 'title' => $movie['title']]; }, $all_movies)); ?>;
            
            document.getElementById('actorSelect').addEventListener('click', function() {
                populateActors(allActors);
            });
            
            document.getElementById('movieSelect').addEventListener('click', function() {
                populateMovies(allMovies);
            });
        });
    </script>
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
                    <a class="nav-link" href="Backend_Implementation/api/auth/logout.php">Logout</a>
                </li>
            </ul>
            <a class="back-button" href="userMainPage.php">Back</a>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <select id="actorSelect" class="custom-select" onchange="fetchFilteredMovies()">
                    <option value="">Choose an Actor</option>
                    <option value="all">All Actors</option>
                    <?php foreach ($all_actors as $actor): ?>
                        <option value="<?php echo htmlspecialchars($actor); ?>"><?php echo htmlspecialchars($actor); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <select id="movieSelect" class="custom-select" onchange="fetchFilteredMovies()">
                    <option value="">Choose a Film</option>
                    <option value="all">All Movies</option>
                    <?php foreach ($all_movies as $movie): ?>
                        <option value="<?php echo $movie['id']; ?>"><?php echo htmlspecialchars($movie['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (count($watchlist_movies) > 0): ?>
            <?php foreach (array_chunk($watchlist_movies, 2) as $movie_pair): ?>
                <div class="row movie-row-divider">
                    <?php foreach ($movie_pair as $movie): ?>
                        <div class="col-md-6">
                            <div class="movie-card">
                                <img src="<?php echo htmlspecialchars($movie['image_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                <div class="movie-info">
                                    <h4><a href="movieDescription.php?id=<?php echo $movie['id']; ?>"><?php echo htmlspecialchars($movie['title']); ?></a></h4>
                                    <p><?php echo htmlspecialchars($movie['genres']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> min</p>
                                    <p class="imdb-rating">IMDB: <?php echo htmlspecialchars($movie['rating']); ?></p>
                                    <?php 
                                    $showtimes = fetchShowtimes($conn, $movie['id']);
                                    if (!empty($showtimes)) {
                                        foreach ($showtimes as $showtime) {
                                            echo "<span class='showtime'>" . htmlspecialchars(date('H:i', strtotime($showtime))) . "</span>";
                                        }
                                    } else {
                                        echo "<p>No showtimes available.</p>";
                                    }
                                    ?>
                                    <a href="Backend_Implementation/api/booking/bookTicket.php?movie_id=<?php echo $movie['id']; ?>" class="book-button">Book</a>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                        <button type="submit" name="delete_movie" class="delete-button" onclick="return confirm('Are you sure you want to delete this movie from your watchlist?');">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No movies in your watchlist.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>
