<?php
// Initialize variables to store login status and error messages
$login = false;
$showError = false;

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection file
    include '../../partials/db_connect.php';

    // Get form data
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $num = $result->num_rows;

    // Check if the username and password match a record in the database
    if ($num == 1) {
        // Set login status to true and start a session
        $login = true;
        session_start();
        $row = $result->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = $row['is_admin'];

        // Log successful login
        $user_id = $row['id'];
        $action = "User logged in";
        $stmt_log = $conn->prepare("INSERT INTO system_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
        $stmt_log->bind_param("is", $user_id, $action);
        $stmt_log->execute();
        $stmt_log->close();

        // Redirect user based on their role
        if ($row['is_admin']) {
            header("location: ../../../adminDashboard.php");
        } else {
            header("location: ../../../userMainPage.php");
        }
    } else {
        // Set error message for invalid credentials
        $showError = "Invalid Credentials";

        // Log failed login attempt
        $user_id = null; // Unknown user
        $action = "Failed login attempt for username: $username";
        $stmt_log = $conn->prepare("INSERT INTO system_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
        $stmt_log->bind_param("is", $user_id, $action);
        $stmt_log->execute();
        $stmt_log->close();
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Frontend_Design/css/styles.css">
    <title>Login</title>
    <style>
        body {
            background-color: black;
            color: white;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin-top: -90px;
        }
        .login-box {
            background-color: #4b0082;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            width: 300px;
        }
        .login-box h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-box .form-group label {
            color: white;
        }
        .login-box .form-control {
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .login-box .btn-primary {
            background-color: #6a0dad;
            border: none;
            width: 100%;
        }
        .home-button {
            text-align: right;
            margin-bottom: 10px;
            padding: 10px;
            font-size: 1.5em;
        }
        .home-button a {
            background-color: lightblue;
            color: white;
            padding: 15px 50px;
            border-radius: 17px;
            text-decoration: none;
            transition: transform 0.3s, background-color 0.3s;
            display: inline-block;
        }
        .home-button a:hover {
            color: white;
            background-color: red;
            transform: scale(1.1);
        }
        .signup-link {
            color: white;
            text-align: center;
            margin-top: 10px;
        }
        .signup-link a {
            color: lightblue;
            text-decoration: none;
        }
        .signup-link a:hover {
            color: red;
        }
        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
        .forgot-password a {
            color: lightblue;
            text-decoration: none;
        }
        .forgot-password a:hover {
            color: red;
        }
    </style>
</head>
<body>
    <div class="home-button">
        <a href="../auth/mainPage.php">Home</a>
    </div>
    <div class="login-container">
        <div class="login-box">
            <h1>StreamFlix</h1>
            <?php if ($showError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= $showError ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php endif; ?>
            <form id="loginForm" action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary">LogIn</button>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <div class="forgot-password">
                    <a href="forgotPassword.php">Forgot password?</a>
                </div>
                <div class="signup-link">
                    Don't have an account? <a href="register.php">SignUp</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="../../Frontend_Design/js/validation.js"></script>
</body>
</html>
