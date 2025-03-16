<?php
// Start a session or resume the existing session
session_start();

// Check if the user is logged in, is an admin, and redirect to login page if not
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true || !$_SESSION['is_admin']) {
    header("location: ../auth/login.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Movies Dashboard</title>
    <style>
        body {
            background-color: white;
            color: black;
            font-family: 'DM Sans', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 1200px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 1em;
            margin-right: 10px;
        }
        .back-button {
            background-color: red;
        }
        .home-button {
            background-color: yellow;
            color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Movie List</h1>
        <div class="top-buttons">
            <a href="adminMoviesDashboard.php" class="btn back-button">Back</a>
            <a href="../../adminDashboard.php" class="btn home-button">Home</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Movie Title</th>
                    <th>Genres</th>
                    <th>Actors</th>
                    <th>Cinema</th>
                </tr>
            </thead>
            <tbody id="moviesTableBody">
                <!-- Table rows will be inserted here by JavaScript -->
            </tbody>
        </table>
    </div>

    <script>
        function fetchMovies() {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "fetchMovies.php", true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const movies = JSON.parse(this.responseText);
                    let output = '';
                    movies.forEach(function (movie) {
                        output += `
                            <tr>
                                <td>${movie.title}</td>
                                <td>${movie.genres}</td>
                                <td>${movie.actors}</td>
                                <td>${movie.cinema}</td>
                            </tr>
                        `;
                    });
                    document.getElementById('moviesTableBody').innerHTML = output;
                }
            }
            xhr.send();
        }

        // Fetch movies every 5 seconds
        setInterval(fetchMovies, 5000);

        // Initial fetch
        fetchMovies();
    </script>
</body>
</html>
