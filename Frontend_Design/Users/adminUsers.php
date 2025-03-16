<?php
// Start a session or resume the existing session
session_start();

// Check if the user is logged in by verifying if 'user_id' exists in the session
$is_logged_in = isset($_SESSION['user_id']);

// Check if the logged-in user is an admin by verifying 'is_admin' in the session
$is_admin = $is_logged_in && isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;

// If the user is not logged in or not an admin, redirect to the admin dashboard
if (!$is_logged_in || !$is_admin) {
    header("Location: ../../adminDashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List - CineSphere</title>
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
        <h1>User List</h1>
        <div class="top-buttons">
            <a href="adminUsersDashboard.php" class="btn back-button">Back</a>
            <a href="../../adminDashboard.php" class="btn home-button">Home</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Email</th>
                    <th>Date of Birth</th>
                    <th>Phone Number</th>
                    <th>Movies Viewed</th>
                </tr>
            </thead>
            <tbody id="user-table-body">
                <!-- Data will be injected here by JavaScript -->
            </tbody>
        </table>
    </div>
    <script>
        async function fetchUsers() {
            try {
                const response = await fetch('fetchUsers.php');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const users = await response.json();
                const tableBody = document.getElementById('user-table-body');
                tableBody.innerHTML = '';

                users.forEach(user => {
                    const row = document.createElement('tr');

                    const usernameCell = document.createElement('td');
                    usernameCell.textContent = user.username;
                    row.appendChild(usernameCell);

                    const passwordCell = document.createElement('td');
                    passwordCell.textContent = user.password;
                    row.appendChild(passwordCell);

                    const emailCell = document.createElement('td');
                    emailCell.textContent = user.email;
                    row.appendChild(emailCell);

                    const dobCell = document.createElement('td');
                    dobCell.textContent = user.date_of_birth;
                    row.appendChild(dobCell);

                    const phoneCell = document.createElement('td');
                    phoneCell.textContent = user.phone_number;
                    row.appendChild(phoneCell);

                    const moviesCell = document.createElement('td');
                    moviesCell.textContent = user.movies.join(', ');
                    row.appendChild(moviesCell);

                    tableBody.appendChild(row);
                });
            } catch (error) {
                console.error('Error fetching users:', error);
            }
        }

        // Fetch users every 5 seconds
        setInterval(fetchUsers, 5000);

        // Initial fetch
        fetchUsers();
    </script>
</body>
</html>
