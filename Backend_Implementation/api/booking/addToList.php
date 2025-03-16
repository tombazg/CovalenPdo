<?php
// Start a session or resume the existing session
session_start();

// Include the database connection file
include 'Backend_Implementation/partials/db_connect.php';

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Check if the request method is POST and movie_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
    $movie_id = $_POST['movie_id'];

    // Check if the movie is already in the user's watchlist
    $sql_check = "SELECT * FROM watchlist WHERE user_id = $user_id AND movie_id = $movie_id";
    $result_check = mysqli_query($conn, $sql_check);

    // If the movie is not in the watchlist, add it
    if (mysqli_num_rows($result_check) == 0) {
        // SQL query to add the movie to the watchlist
        $sql_add = "INSERT INTO watchlist (user_id, movie_id) VALUES ($user_id, $movie_id)";
        if (mysqli_query($conn, $sql_add)) {
            // Fetch the movie title for logging purposes
            $sql_movie = "SELECT title FROM movies WHERE id = $movie_id";
            $result_movie = mysqli_query($conn, $sql_movie);
            if ($result_movie) {
                $movie = mysqli_fetch_assoc($result_movie);
                $movie_title = $movie['title'];

                // Log the action in system_logs
                $action = "Added movie \"$movie_title\" to watchlist";
                $stmt_log = $conn->prepare("INSERT INTO system_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
                if ($stmt_log) {
                    // Bind parameters and execute the log statement
                    $stmt_log->bind_param("is", $user_id, $action);
                    if ($stmt_log->execute()) {
                        echo "Log entry added successfully.";
                        $stmt_log->close();
                    } else {
                        // Error executing log statement
                        echo "Error executing log statement: " . $stmt_log->error;
                    }
                } else {
                    // Error preparing log statement
                    echo "Error preparing log statement: " . $conn->error;
                }
            } else {
                // Error fetching movie title
                echo "Error fetching movie title: " . mysqli_error($conn);
            }

            // Redirect to the addToList page
            header("Location: addToList.php");
            exit;
        } else {
            // Error adding movie to watchlist
            echo "Error adding movie to watchlist: " . mysqli_error($conn);
        }
    } else {
        // Message if the movie is already in the watchlist
        echo "Movie is already in your watchlist.";
    }
}

// Fetch movies in the user's watchlist
$sql_watchlist = "SELECT movies.* FROM watchlist 
                  JOIN movies ON watchlist.movie_id = movies.id 
                  WHERE watchlist.user_id = $user_id";
$result_watchlist = mysqli_query($conn, $sql_watchlist);
$watchlist_movies = mysqli_fetch_all($result_watchlist, MYSQLI_ASSOC);
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
            right: 20px;
            background-color: yellow;
            color: #444;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .book-button:hover {
            background-color: #CC5500;
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
                    <a class="nav-link" href="addToList.php">Watchlist</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Backend_Implementation/api/auth/logout.php">Logout</a>
                </li>
            </ul>
            <a class="back-button" href="userMainPage.php">Back</a>
        </div>
    </nav>

    <div class="container">
        <h1>Your Watchlist</h1>
        <div class="row">
            <?php foreach ($watchlist_movies as $movie): ?>
                <div class="col-md-6">
                    <div class="movie-card">
                        <img src="<?php echo htmlspecialchars($movie['image_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <div class="movie-info">
                            <h4><a href="movieDescription.php?id=<?php echo $movie['id']; ?>"><?php echo htmlspecialchars($movie['title']); ?></a></h4>
                            <p><?php echo htmlspecialchars($movie['genres']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> min</p>
                            <p class="imdb-rating">IMDB: <?php echo htmlspecialchars($movie['rating']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>
