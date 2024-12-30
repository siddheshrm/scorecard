<?php
include 'session_handler.php';
include 'config.php';

// Check if the logged-in user is a superadmin
$logged_in_username = $_SESSION['username'] ?? '';
$superadmin_username = 'SUPER_ADMIN'; // Replace with your super admin's username

if (strcasecmp($logged_in_username, $superadmin_username) !== 0) {
    echo '<script>alert("You do not have the necessary permissions to create an admin account.")</script>';
    echo '<script>window.location.href = "admin_dashboard.php";</script>';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $age = $_POST['age'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (strlen($name) < 5 || preg_match('/[^a-zA-Z ]/', $name)) {
        echo '<script>alert("Name must be at least 5 characters long and contain only letters and spaces.")</script>';
    } elseif (strlen($username) < 5 || preg_match('/[^A-Za-z0-9_]/', $username)) {
        echo '<script>alert("Username must be at least 5 characters long and contain only letters, digits, and underscores.")</script>';
    } elseif (empty($age) || $age < 13 || $age > 99) {
        echo '<script>alert("Age must be between 13 and 99.")</script>';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("Invalid email address.")</script>';
    } elseif (strlen($password) < 8) {
        echo '<script>alert("Password must be at least 8 characters long.")</script>';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(username) = ?");
        $stmt->bind_param("s", $username);
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
                echo '<script>alert("Email already exists. Please use a different email to create an account.")</script>';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO users (username, name, age, email, password) VALUES (?, ?, ?, ?, ?)");
                // Bind parameters
                $stmt->bind_param("ssiss", $username, $name, $age, $email, $hashed_password);

                try {
                    if ($stmt->execute()) {
                        // Redirect with success message
                        echo '<script>alert("Admin registered successfully.");</script>';
                        echo '<script>window.location.href = "admin_dashboard.php";</script>';
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
?>