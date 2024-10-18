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
    <h3>Enter Details To Register</h3>
    <form method="POST" action="register.php">
        <input type="text" name="name" placeholder="Username*" required><br>
        <input type="number" name="age" placeholder="Age*" required min="13" max="99"><br>
        <input type="email" name="email" placeholder="Email*" required><br>
        <input type="text" name="favourite_ipl_team" placeholder="Favourite IPL Team"><br>
        <input type="text" name="mobile_number" placeholder="Mobile Number" maxlength="10"><br>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <span id="togglePassword" class="toggle-password">Show</span>
        </div><br>
        <input type="submit" value="Submit">
    </form>

    <p>Already have an account? <a href="index.php">Login here</a></p><br>

    <p><a href="strike_rate/strike_rate.php">Strike Rate Calculator</a></p>
    <p><a href="dls/dls_calculator.php">Duckworth-Lewis-Stern Par Score Calculator</a></p><br>

    <!-- Include Trivia -->
    <?php include './trivia/trivia.php'; ?>

    <!-- Include Register Validation -->
    <?php include 'register_validation.php'; ?>

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
</body>

</html>