<?php
include '../config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate token and check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: ../password_recovery/reset_password.php?token=' . urlencode($token));
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        header('Location: ../password_recovery/reset_password.php?token=' . urlencode($token));
        exit();
    }

    $sql = "SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Invalid or expired request. Please create a new request to update the password.';
        header('Location: ../password_recovery/reset_password.php?token=' . urlencode($token));
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashed_password, $token);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Your password has been reset successfully. You can now login.';
        header('Location: ../index.php');
    } else {
        $_SESSION['error'] = 'An error occurred. Please try again.';
        header('Location: ../password_recovery/reset_password.php?token=' . urlencode($token));
    }
    $stmt->close();
}
$conn->close();
