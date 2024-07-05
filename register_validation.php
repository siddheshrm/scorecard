<?php
// Database connection
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and validate input
    $name = strtolower($_POST['name']);
    $age = $_POST['age'];
    $email = isset($_POST['email']) ? $_POST['email'] : null; // Handle optional email
    $mobile_number = isset($_POST['mobile_number']) ? $_POST['mobile_number'] : null; // Handle optional mobile number
    $favourite_ipl_team = $_POST['favourite_ipl_team'];
    $password = $_POST['password'];

    // Validate input
    if (strlen($name) < 4 || preg_match('/[\d\W]/', $name)) {
        echo '<script>alert("Name must be at least 4 characters long and contain only letters and underscores")</script>';
    } elseif (empty($age) || $age < 13 || $age > 99) {
        echo '<script>alert("Age must be between ' . 13 . ' and ' . 99 . '")</script>';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("Invalid Email Address")</script>';
    } elseif (!empty($mobile_number) && !preg_match('/^\d{10}$/', $mobile_number)) {
        echo '<script>alert("Mobile number must be exactly 10 digits if provided")</script>';
    } elseif (strlen($password) < 5) {
        echo '<script>alert("Password must be at least 5 characters long")</script>';
    } else {
        // Check if the username already exists (case-insensitive)
        $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(name) = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo '<script>alert("Username already exists. Please choose a different username.")</script>';
        } else {
            // Prepare SQL query
            $stmt = $conn->prepare("INSERT INTO users (name, age, email, mobile_number, favourite_ipl_team, password) VALUES (?, ?, ?, ?, ?, ?)");
            // Bind parameters
            if (empty($email) && empty($mobile_number)) {
                $stmt->bind_param("sissss", $name, $age, $nullValue, $nullValue, $favourite_ipl_team, $password);
            } elseif (empty($email) && !empty($mobile_number)) {
                $stmt->bind_param("sissss", $name, $age, $nullValue, $mobile_number, $favourite_ipl_team, $password);
            } elseif (!empty($email) && empty($mobile_number)) {
                $stmt->bind_param("sissss", $name, $age, $email, $nullValue, $favourite_ipl_team, $password);
            } else {
                $stmt->bind_param("sissss", $name, $age, $email, $mobile_number, $favourite_ipl_team, $password);
            }


            try {
                if ($stmt->execute()) {
                    // Redirect with success message
                    session_start();
                    echo '<script>alert("New record created successfully.")</script>';
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

    $conn->close();
}
