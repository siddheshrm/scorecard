<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register here</title>
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
        <input type="password" name="password" placeholder="Password*" required><br>
        <input type="submit" value="Submit">
    </form>

    <p>Already have an account? <a href="index.php">Login here</a></p><br>

    <p><a href="strike rate/strike_rate.php">Strike Rate Calculator</a></p>
    <p><a href="dls/dls_calculator.php">Duckworth-Lewis-Stern Par Score Calculator</a></p><br>

    <!-- Include Trivia -->
    <?php include 'trivia.php'; ?>

    <!-- Include Register Validation -->
    <?php include 'register_validation.php'; ?>
</body>

</html>