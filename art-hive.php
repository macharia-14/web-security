<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    // User is not logged in, redirect to the login page
    header('Location: login.php');
    exit();
}

// User is logged in, display the home page
echo "Welcome to the home page!";
?>
