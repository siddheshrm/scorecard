<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strike Rate Calculator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tuffy:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../media/logos/IPL.png" type="image/png">
    <link rel="stylesheet" href="../css/common.css">
</head>

<body>
    <h2>Batting Strike Rate Calculator</h2>

    <p>
        Batting strike rate (s/r) is defined for a batter as the average number of runs scored per 100 balls faced. The
        higher the strike rate, the more effective a batter is at scoring quickly.
    </p>

    <form method="POST">
        <div class="section row-group">
            <div class="form-group">
                <label for="runs">Runs Scored<span style="color: red;">*<span /></label>
                <input type="number" id="runs" name="runs" required>
            </div>

            <div class="form-group">
                <label for="balls">Balls Faced<span style="color: red;">*<span /></label>
                <input type="number" id="balls" name="balls" required>
            </div>
        </div>

        <input type="submit" value="Calculate">
    </form>
    <hr>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve values from form submission
        $balls = $_POST["balls"];
        $runs = $_POST["runs"];

        // Check if both values are provided
        if (!empty($balls) && !empty($runs)) {
            // Calculate strike rate
            $strikeRate = ($runs / $balls) * 100;
            // Format the strike rate to 2 decimal places
            $strikeRateFormatted = number_format($strikeRate, 2);
            echo "<b>$runs Runs scored in $balls balls with the Strike Rate of $strikeRateFormatted</b>";
        } else {
            echo "Please fill in both fields.";
        }
    }
    ?>

    <p><a href="../index.php">Go to homepage</a></p><br>

    <!-- Include Trivia -->
    <?php include '../trivia/trivia.php'; ?>

</body>

</html>