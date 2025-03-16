<?php
// Start a session or resume the existing session
session_start();

// Include the database connection file
include 'Backend_Implementation/partials/db_connect.php';

// Fetch movies from the database with optional filters
function fetchMovies($conn, $cinema_id = null, $category = null, $movie_id = null) {
    $sql = "SELECT m.* FROM movies m";
    
    if ($cinema_id !== null && $cinema_id !== 'all') {
        $sql .= " JOIN cinema_movies cm ON m.id = cm.movie_id WHERE cm.cinema_id = $cinema_id";
    } elseif ($category !== null && $category !== 'all') {
        $sql .= " WHERE FIND_IN_SET('$category', m.category)";
    } elseif ($movie_id !== null && $movie_id !== 'all') {
        $sql .= " WHERE m.id = $movie_id";
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

// Get filter parameters from the URL
$cinema_id = isset($_GET['cinema_id']) ? $_GET['cinema_id'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : null;

// Fetch movies based on the filter parameters
$movies = fetchMovies($conn, $cinema_id, $category, $movie_id);

// Fetch cinemas from the database
$sql_cinemas = "SELECT * FROM cinemas";
$result_cinemas = mysqli_query($conn, $sql_cinemas);

$cinemas = [];
if ($result_cinemas) {
    while ($row = mysqli_fetch_assoc($result_cinemas)) {
        $cinemas[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($conn);
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

// Fetch all categories from the movies
$all_categories = [];
foreach ($movies as $movie) {
    $movie_categories = explode(',', $movie['category']);
    foreach ($movie_categories as $category) {
        if (!in_array(trim($category), $all_categories)) {
            $all_categories[] = trim($category);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <title>CineSphere</title>
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
        .search-bar {
            width: 300px;
        }
        .search-results {
            position: absolute;
            background: #fff;
            color: #000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            z-index: 1000;
        }
        .search-result-item {
            padding: 10px;
            cursor: pointer;
        }
        .search-result-item:hover {
            background-color: #eee;
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
        .form-inline .form-control {
            margin-right: 10px;
        }
        .row .col-md-6 {
            padding: 10px;
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
        .movie-row-divider {
            border-top: 1px solid #fff;
            margin-top: 20px;
            padding-top: 20px;
        }
    </style>
    <script>
        function fetchFilteredMovies() {
            const cinemaId = document.getElementById('cinemaSelect').value;
            const category = document.getElementById('categorySelect').value;
            const movieId = document.getElementById('movieSelect').value;

            let queryString = '';

            if (cinemaId && cinemaId !== 'all') {
                queryString += `cinema_id=${cinemaId}&`;
            }

            if (category && category !== 'all') {
                queryString += `category=${category}&`;
            }

            if (movieId && movieId !== 'all') {
                queryString += `movie_id=${movieId}&`;
            }

            window.location.href = `userMainPage.php?${queryString}`;
        }

        function populateCategories(categories) {
            const categorySelect = document.getElementById('categorySelect');
            categorySelect.innerHTML = '<option value="">Choose a Screening Type</option><option value="all">All Movies</option>';
            categories.forEach(category => {
                categorySelect.innerHTML += `<option value="${category}">${category}</option>`;
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
            const allCategories = <?php echo json_encode($all_categories); ?>;
            const allMovies = <?php echo json_encode(array_map(function($movie) { return ['id' => $movie['id'], 'title' => $movie['title']]; }, $movies)); ?>;
            
            document.getElementById('categorySelect').addEventListener('click', function() {
                populateCategories(allCategories);
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
                    <a class="nav-link" href="watchlist.php">Watchlist</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Backend_Implementation/api/auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>
        <form class="form-inline my-2 my-lg-0" onsubmit="return false;">
            <div search-wrapper>
                <input id="searchInput" class="form-control mr-sm-2 search-bar" type="search" placeholder="Search movies or actors..." aria-label="Search">
                <div id="searchResults" class="search-results"></div>
            </div>
        </form>
    </nav>

    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <select id="cinemaSelect" class="custom-select" onchange="fetchFilteredMovies()">
                    <option value="">Choose a Cinema</option>
                    <option value="all">All Cinemas</option>
                    <?php foreach ($cinemas as $cinema): ?>
                        <option value="<?php echo $cinema['id']; ?>"><?php echo htmlspecialchars($cinema['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <select id="categorySelect" class="custom-select" onchange="fetchFilteredMovies()">
                    <option value="">Choose a Screening Type</option>
                    <option value="all">All Movies</option>
                    <?php foreach ($all_categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <select id="movieSelect" class="custom-select" onchange="fetchFilteredMovies()">
                    <option value="">Choose a Film</option>
                    <option value="all">All Movies</option>
                    <?php foreach ($movies as $movie): ?>
                        <option value="<?php echo $movie['id']; ?>"><?php echo htmlspecialchars($movie['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php foreach (array_chunk($movies, 2) as $movie_pair): ?>
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
                                <a href="Backend_Implementation/api/booking/bookTicket.php" class="book-button" style="color: #444">Book</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById('searchInput');
            const searchResultsContainer = document.getElementById('searchResults');

            let debounceTimeout;

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    const query = searchInput.value.trim();
                    console.log('Search query:', query); // Log the query
                    if (query.length > 0) {
                        fetchSearchResults(query);
                    } else {
                        searchResultsContainer.innerHTML = '';
                    }
                }, 300);
            });

            function fetchSearchResults(query) {
                console.log('Fetching search results for:', query); // Log before fetch
                fetch(`search.php?q=${query}`)
                    .then(response => {
                        console.log('Fetch response:', response); // Log the response
                        return response.json();
                    })
                    .then(data => {
                        console.log('Search results:', data); // Log the data
                        displaySearchResults(data);
                    })
                    .catch(error => {
                        console.error('Error fetching search results:', error);
                    });
            }

            function displaySearchResults(results) {
                searchResultsContainer.innerHTML = '';
                results.forEach(result => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.textContent = result.name;
                    resultItem.addEventListener('click', () => {
                        window.location.href = result.type === 'movie' ? `movieDescription.php?id=${result.id}` : `actorDescription.php?id=${result.id}`;
                    });
                    searchResultsContainer.appendChild(resultItem);
                });
            }
        });
    </script>
</body>
</html>
