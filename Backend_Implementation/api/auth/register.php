<?php
// Initialize variables to store alert and error messages
$showAlert = false;
$showError = false;

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection file
    include '../../partials/db_connect.php';

    // Get form data
    $username = $_POST["username"];
    $password = $_POST["password"];
    $cpassword = $_POST["cpassword"];
    $email = $_POST["email"];
    $date_of_birth = $_POST["date_of_birth"];
    $phone_number = $_POST["phone_number"];

    // Check if passwords match
    if ($password == $cpassword) {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
        if (!$stmt) {
            // Set error message if statement preparation fails
            $showError = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        } else {
            // Bind parameters and execute the statement
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if the username already exists
            if ($result->num_rows > 0) {
                $showError = "Username already exists";
            } else {
                // Prepare an insert statement to add a new user
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, date_of_birth, phone_number, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
                if (!$stmt) {
                    // Set error message if statement preparation fails
                    $showError = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                } else {
                    // Bind parameters and execute the insert statement
                    $stmt->bind_param("sssss", $username, $password, $email, $date_of_birth, $phone_number);
                    if ($stmt->execute()) {
                        // Get the user ID of the newly created user
                        $user_id = $stmt->insert_id;

                        // Log the signup action
                        $action = "New user signup: $username";
                        $stmt_log = $conn->prepare("INSERT INTO system_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
                        if (!$stmt_log) {
                            // Set error message if log statement preparation fails
                            $showError = "Log prepare failed: (" . $conn->errno . ") " . $conn->error;
                        } else {
                            // Bind parameters and execute the log statement
                            $stmt_log->bind_param("is", $user_id, $action);
                            if ($stmt_log->execute()) {
                                // Start a session and set session variables
                                session_start();
                                $_SESSION['loggedin'] = true;
                                $_SESSION['user_id'] = $user_id;
                                $_SESSION['username'] = $username;
                                $_SESSION['is_admin'] = 0; // Ensure the session variable is set

                                // Redirect to the user main page
                                header("Location: ../../../userMainPage.php");
                                exit;
                            } else {
                                // Set error message if log statement execution fails
                                $showError = "Log execute failed: (" . $stmt_log->errno . ") " . $stmt_log->error;
                            }
                            $stmt_log->close();
                        }
                    } else {
                        // Set error message if insert statement execution fails
                        $showError = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                    }
                }
            }
            $stmt->close();
        }
    } else {
        // Set error message if passwords do not match
        $showError = "Passwords do not match";
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
    <title>Sign Up</title>
    <style>
        body {
            background-color: black;
            color: white;
        }
        .signup-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin-top: -80px;
        }
        .signup-box {
            background-color: green;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            width: 400px;
        }
        .signup-box h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .signup-box .form-group label {
            color: white;
        }
        .signup-box .form-control {
            background-color: #777;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .signup-box .form-control::placeholder {
            color: lightgrey;
        }
        .signup-box .btn-primary {
            background-color: #90EE90;
            color: #777;
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
        .form-text-red {
            color: red; /* Change small text to red */
        }
    </style>
</head>
<body>
    <div class="home-button">
         <a href="../auth/mainPage.php">Home</a>
    </div>
    <div class="signup-container">
        <div class="signup-box">
            <h1>CineSphere</h1>
            <?php if ($showAlert): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Your account is now created and you can log in.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php endif; ?>
            <?php if ($showError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= htmlspecialchars($showError) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php endif; ?>
            <form id="signupForm" action="register.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label for="cpassword">Confirm Password</label>
                    <input type="password" class="form-control" id="cpassword" name="cpassword" placeholder="Confirm Password" required>
                    <small class="form-text form-text-red">Make sure to type the same password</small>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Phone Number" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="../../Frontend_Design/js/validation.js"></script>
</body>
</html>
