<?php
// Initialize variables to store error and success messages
$showError = false;
$showSuccess = false;

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection file
    include '../../partials/db_connect.php';

    // Get form data
    $username = $_POST["username"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Check if new password and confirm password match
    if ($new_password === $confirm_password) {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $num = $result->num_rows;

        // Check if the username exists in the database
        if ($num == 1) {
            // Prepare an update statement to change the password
            $stmt_update = $conn->prepare("UPDATE users SET password=? WHERE username=?");
            $stmt_update->bind_param("ss", $new_password, $username); // Using plain text password

            // Execute the update statement
            if ($stmt_update->execute()) {
                // Set success message if the password is updated successfully
                $showSuccess = "Password updated successfully.";

                // Log the password reset action
                $row = $result->fetch_assoc();
                $user_id = $row['id'];
                $action = "Password reset for username: $username";
                $stmt_log = $conn->prepare("INSERT INTO system_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
                $stmt_log->bind_param("is", $user_id, $action);
                $stmt_log->execute();
                $stmt_log->close();
            } else {
                // Set error message if the password update fails
                $showError = "Error updating password. Please try again.";
            }
            $stmt_update->close();
        } else {
            // Set error message if the username is not found
            $showError = "Username not found.";
        }
        $stmt->close();
    } else {
        // Set error message if the passwords do not match
        $showError = "Passwords do not match.";
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
    <link rel="stylesheet" href="../../Frontend_Design/css/styles.css">
    <title>Reset Your Password</title>
    <style>
        body {
            background-color: black;
            color: #333;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin-top: -90px;
        }
        .login-box {
            background-color: #007bff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            width: 300px;
            color: white;
        }
        .login-box h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-box .form-group label {
            color: white;
        }
        .login-box .form-control {
            background-color: #777;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .login-box .form-control::placeholder {
            color: lightgrey;
        }
        .login-box .btn-primary {
            background-color: #0056b3;
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
            background-color: #007bff;
            color: white;
            padding: 15px 50px;
            border-radius: 17px;
            text-decoration: none;
            transition: transform 0.3s, background-color 0.3s;
            display: inline-block;
        }
        .home-button a:hover {
            color: white;
            background-color: #0056b3;
            transform: scale(1.1);
        }
        .signup-link {
            color: white;
            text-align: center;
            margin-top: 10px;
        }
        .signup-link a {
            color: #add8e6;
            text-decoration: none;
        }
        .signup-link a:hover {
            color: #0056b3;
        }
        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
        .forgot-password a {
            color: #add8e6; 
            text-decoration: none;
        }
        .forgot-password a:hover {
            color: #0056b3; 
        }
        .alert {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="home-button">
        <a href="../auth/mainPage.php">Home</a>
    </div>
    <div class="login-container">
        <div class="login-box">
            <h1>Reset Your Password</h1>
            <?php if ($showError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= $showError ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php endif; ?>
            <?php if ($showSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?= $showSuccess ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php endif; ?>
            <form id="resetForm" action="forgotPassword.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="../../Frontend_Design/js/validation.js"></script>
</body>
</html>
