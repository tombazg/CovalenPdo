<?php
session_start();
if (isset($_SESSION['user_id'])) {
    include '../../partials/db_connect.php';
    
    // Get the user ID and username from the session
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Log the logout action
    $action = "User logout: $username";
    $stmt_log = $conn->prepare("INSERT INTO system_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
    if ($stmt_log) {
        $stmt_log->bind_param("is", $user_id, $action);
        $stmt_log->execute();
        $stmt_log->close();
    } else {
        error_log("Failed to prepare statement for logging logout: (" . $conn->errno . ") " . $conn->error);
    }
    
    // Close the database connection
    $conn->close();
}

// Unset all session variables and destroy the session
session_unset();
session_destroy();

// Redirect to the main page
header("Location: ../auth/mainPage.php");
exit;
?>
