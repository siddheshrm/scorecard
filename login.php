<?php
session_start();

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query the database to check if the user exists
    $sql = "SELECT * FROM users WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

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
                exit();
            } else {
                header("Location: user_dashboard.php");
                exit();
            }
        } else {
            // Incorrect password
            echo '<script>alert("Incorrect password.")</script>';
            echo '<script>window.location.href = "index.php";</script>';
            exit();
        }
    } else {
        // User does not exist
        echo '<script>alert("User does not exist.")</script>';
        echo '<script>window.location.href = "index.php";</script>';
        exit();
    }
}

$conn->close();
