<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to scorecard.com</title>
    <link rel="icon" href="./media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h2>Welcome to <i>scorecard.com</i></h2>
    <form method="POST" action="login.php">
        <label for="username"></label>
        <input type="text" id="username" name="username" placeholder="Username" required><br>
        <label for="password"></label>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <span id="togglePassword" class="toggle-password">Show</span>
        </div><br>
        <input type="submit" value="Login">
    </form>

    <p>Don't have an account? <a href="register.php">Sign up here.</a></p>
    <p><a href="password_recovery/forgot_password.php">Forgot Password?</a></p><br>

    <p><a href="strike_rate/strike_rate.php">Strike Rate Calculator</a></p>
    <p><a href="dls/dls_calculator.php">Duckworth-Lewis-Stern Par Score Calculator</a></p><br>

    <!-- Include Trivia -->
    <?php include 'trivia.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.textContent = type === 'password' ? 'Show' : 'Hide';
            });
        });
    </script>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<script>alert('" . htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['message']);
    }
    ?>
</body>

</html>