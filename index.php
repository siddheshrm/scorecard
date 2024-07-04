<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- <link rel="stylesheet" href="css/index.css"> -->
</head>

<body>
    <h2>Welcome to <i>scorecard.com</i></h2>
    <form method="POST" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>

    <p><a href="register.php">Don't have an account?</a></p>
    <p><a href="#" onclick="showMessage()">Forgot Password?</a></p><br>

    <p><a href="strike rate/strike_rate.php">Strike Rate Calculator</a></p>
    <p><a href="dls/dls_calculator.php">Duckworth-Lewis-Stern Par Score Calculator</a></p><br>

    <?php
    function fetchTrivia($id)
    {
        $url = "https://cricket-trivia-api-2fa7961781d4.herokuapp.com/cricket_trivia";
        $data = file_get_contents($url);
        $triviaArray = json_decode($data, true);

        foreach ($triviaArray as $trivia) {
            if ($trivia['id'] == $id) {
                return $trivia;
            }
        }
        return null;
    }

    $today = date("j"); // Day of the month without leading zeros
    $trivia = fetchTrivia($today);

    if ($trivia) {
        echo "<h3>Cricket Trivia for Today</h3>";
        echo "<p><strong>" . $trivia['title'] . ":</strong> " . $trivia['description'] . "</p>";
    } else {
        echo "<p>No trivia found for today.</p>";
    }
    ?>

    <script>
        function showMessage() {
            alert("Enter today's date in DD-MM-YYYY format as password to login");
        }
    </script>
</body>

</html>