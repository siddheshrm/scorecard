<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register to scorecard.com</title>
    <link rel="icon" href="./media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <h2>Welcome to <i>scorecard.com</i></h2>
    <h3>Register New Admin</h3>
    <form method="POST" action="register.php">
        <input type="text" name="name" placeholder="name*" required><br>
        <input type="text" name="username" placeholder="username*" required><br>
        <input type="number" name="age" placeholder="age*" required min="13" max="99"><br>
        <input type="email" name="email" placeholder="email*" required><br>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="password*" required>
            <span id="togglePassword" class="toggle-password">Show</span>
        </div><br>
        <input type="submit" value="Create New Admin">
    </form>

    <p><a href="admin_dashboard.php">Go To Dashboard</a></p><br>

    <!-- Include Register Validation -->
    <?php include 'register_validation.php'; ?>

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
</body>

</html>