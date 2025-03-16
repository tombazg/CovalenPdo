<?php
// Start a new session or resume the existing session
session_start();

// Check if the user is logged in by verifying if 'user_id' exists in the session
$is_logged_in = isset($_SESSION['user_id']);

// Check if the logged-in user is an admin by verifying 'is_admin' in the session
// If the user is logged in and 'is_admin' is set, use its value; otherwise, set it to false
$is_admin = $is_logged_in && isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineSphere</title>
    <style>
        body {
            background-color: black;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'DM Sans', sans-serif;
        }

        h1 {
            font-size: 5em;
            margin-bottom: 1em;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 1em;
            margin-bottom: 12em;
        }

        .button-container a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 200px;
            height: 200px;
            font-size: 2em;
            font-weight: bold;
            color: #ccc;
            text-decoration: none;
            border-radius: 10px;
            transition: transform 0.3s;
        }

        .button-container a:hover {
            transform: scale(1.1);
        }

        .button-blue {
            background-color: blue;
        }

        .button-green {
            background-color: green;
        }

        .button-purple {
            background-color: purple;
        }

        .button-red {
            background-color: red;
        }

        .button-yellow {
            background-color: yellow;
        }
    </style>
</head>
<body>
    <h1>CineSphere! Welcome!</h1>
    <div class="button-container">
        <?php if (!$is_logged_in): ?>
            <a href="../auth/register.php" class="button button-green">Signup</a>
            <a href="../auth/login.php" class="button button-purple">Login</a>
        <?php else: ?>
            <a href="logout.php" class="button button-red">Logout</a>
            <a href="forgotPassword.php" class="button button-green">Password</a>
            <a href="../../../userMainPage.php" class="button button-yellow">Cinema</a>
            <?php if ($is_admin): ?>
                <a href="../views/admin.php" class="button button-blue">Admin</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
