<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Query the database to check if the user exists
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];
        $email = $row['email'];

        // Verify the hashed password
        if (password_verify($password, $stored_password)) {
            // Store the username and email in session
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            // Redirect to the dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Incorrect password
            echo '<script>alert("Incorrect password.")</script>';
            echo '<script>window.location.href = "admin_login.php";</script>';
            exit();
        }
    } else {
        // User does not exist
        echo '<script>alert("User does not exist.")</script>';
        echo '<script>window.location.href = "admin_login.php";</script>';
        exit();
    }
}
$conn->close();
