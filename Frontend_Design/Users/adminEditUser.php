<?php
// Start a session or resume the existing session
session_start();

// Check if the user is logged in, is an admin, and redirect to login page if not
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true || !$_SESSION['is_admin']) {
    header("location: ../auth/login.php");
    exit;
}

// Initialize a variable to store messages
$message = '';

// Include the database connection file
include '../../Backend_Implementation/partials/db_connect.php';

// Fetch all users for the dropdown
$users = [];
$result = $conn->query("SELECT id, username FROM users");
if ($result->num_rows > 0) {
    // Fetch each row and add it to the users array
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// If a user is selected, fetch their details
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$user = null;
if ($user_id) {
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
    $stmt->close();
}

// Handle form submission for updating a user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $user_id = $_POST["user_id"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $date_of_birth = $_POST["date_of_birth"];
    $phone_number = $_POST["phone_number"];
    $is_admin = isset($_POST["is_admin"]) ? 1 : 0;

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, email = ?, date_of_birth = ?, phone_number = ?, is_admin = ? WHERE id = ?");
    $stmt->bind_param("sssssii", $username, $password, $email, $date_of_birth, $phone_number, $is_admin, $user_id);

    // Execute the prepared statement and set the message based on the result
    if ($stmt->execute()) {
        $message = "User updated successfully.";
    } else {
        $message = "Error updating user: " . $stmt->error;
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
    <title>Edit User</title>
    <style>
        body {
            background-color: white;
            color: black;
            font-family: 'DM Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        .container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, 
        .form-group textarea,
        .form-group select {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            color: black;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .alert {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            color: white;
            background-color: #ff4c4c;
            text-align: center;
        }
        .alert-info {
            background-color: #5cb85c;
        }
        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .form-row .form-group {
            width: calc(50% - 10px);
        }
        .form-group .tooltip {
            margin-left: 5px;
            color: red;
        }
        .form-group input::placeholder, 
        .form-group textarea::placeholder {
            color: lightgrey;
        }
        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .top-buttons a {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 1em;
            position: relative;
        }
        .back-button {
            background-color: red;
        }
        .back-button::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 2px;
            background-color: #cc0000;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        .back-button:hover::after {
            transform: scaleX(1.5);
        }
        .home-button {
            background-color: yellow;
            color: lightgrey !important;
        }
        .home-button::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 2px;
            background-color: #cccc00;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        .home-button:hover::after {
            transform: scaleX(1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-buttons">
            <a href="adminUsersDashboard.php" class="back-button">Back</a>
            <a href="../../adminDashboard.php" class="home-button">Home</a>
        </div>
        <h1>Edit User</h1>
        <?php if ($message): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($message) ?>
                <button onclick="window.location.href='adminUsersDashboard.php'">OK</button>
            </div>
        <?php endif; ?>
        <form id="selectUserForm" method="get" action="adminEditUser.php">
            <div class="form-group">
                <label for="user_id">Select User to Edit</label>
                <select id="user_id" name="user_id" onchange="this.form.submit()" required>
                    <option value="">Select a user</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= htmlspecialchars($u['id']) ?>" <?= $user_id == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <?php if ($user): ?>
        <form id="editUserForm" action="adminEditUser.php?user_id=<?= $user_id ?>" method="post">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" value="<?= htmlspecialchars($user['password']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" placeholder="Date of Birth" value="<?= htmlspecialchars($user['date_of_birth']) ?>">
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" placeholder="Phone Number" value="<?= htmlspecialchars($user['phone_number']) ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="is_admin">Admin</label>
                <input type="checkbox" id="is_admin" name="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>>
            </div>
            <button type="submit" class="btn">Update User</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
