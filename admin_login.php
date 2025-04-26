<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tuffy:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/logos/IPL.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h2>Admin Login</h2>
    <form method="POST" action="login.php">
        <label for="username"></label>
        <input type="text" id="username" name="username" placeholder="username" required><br>
        <label for="password"></label>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="password" required>
            <span id="togglePassword" class="toggle-password">Show</span>
        </div><br>
        <input type="submit" value="Login">
    </form>

    <p>
        <a href="password_recovery/forgot_password.php">Forgot Password</a> | <a href="index.php">Homepage</a>
    </p>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');

            togglePassword.addEventListener('click', function () {
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