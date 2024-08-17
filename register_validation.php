<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strtolower($_POST['name']);
    $age = $_POST['age'];
    $email = $_POST['email'];
    $mobile_number = isset($_POST['mobile_number']) ? $_POST['mobile_number'] : null;
    $favourite_ipl_team = $_POST['favourite_ipl_team'];
    $password = $_POST['password'];

    // Validate input
    if (strlen($name) < 5 || preg_match('/[^a-z0-9_]/', $name)) {
        echo '<script>alert("Username must be at least 5 characters long and contain only letters, digits, and underscores.")</script>';
    } elseif (empty($age) || $age < 13 || $age > 99) {
        echo '<script>alert("Age must be between 13 and 99.")</script>';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("Invalid email address.")</script>';
    } elseif (!empty($mobile_number) && !preg_match('/^\d{10}$/', $mobile_number)) {
        echo '<script>alert("Mobile number must be exactly 10 digits and contain only numbers.")</script>';
    } elseif (strlen($password) < 8) {
        echo '<script>alert("Password must be at least 8 characters long.")</script>';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(name) = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo '<script>alert("Username already exists. Please choose a different username.")</script>';
        } else {
            // Check if email already exists
            $email_lower = strtolower($email);
            $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = ?");
            $stmt->bind_param("s", $email_lower);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo '<script>alert("Email already exists. Please use a different email to create accout.")</script>';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO users (name, age, email, mobile_number, favourite_ipl_team, password) VALUES (?, ?, ?, ?, ?, ?)");
                // Bind parameters
                if (empty($email) && empty($mobile_number)) {
                    $stmt->bind_param("sissss", $name, $age, $nullValue, $nullValue, $favourite_ipl_team, $hashed_password);
                } elseif (empty($email) && !empty($mobile_number)) {
                    $stmt->bind_param("sissss", $name, $age, $nullValue, $mobile_number, $favourite_ipl_team, $hashed_password);
                } elseif (!empty($email) && empty($mobile_number)) {
                    $stmt->bind_param("sissss", $name, $age, $email, $nullValue, $favourite_ipl_team, $hashed_password);
                } else {
                    $stmt->bind_param("sissss", $name, $age, $email, $mobile_number, $favourite_ipl_team, $hashed_password);
                }

                try {
                    if ($stmt->execute()) {
                        // Redirect with success message
                        session_start();
                        echo '<script>alert("Your registration is completed successfully. Please login to continue.")</script>';
                        echo '<script>window.location.href = "index.php";</script>';
                        exit();
                    } else {
                        echo "Error: " . $stmt->error;
                    }
                } catch (mysqli_sql_exception $e) {
                    // Handle specific exceptions, e.g., unique constraint violation
                    echo '<script>alert("Error: Unable to register user. Please try again later.")</script>';
                    // Log the detailed error for debugging
                    error_log("SQL Error: " . $e->getMessage());
                }
                $stmt->close();
            }
        }
    }
    $conn->close();
}
