<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strike Rate Calculator</title>
    <link rel="stylesheet" href="../css/common.css">
</head>

<body>
    <h2>Strike Rate Calculator</h2>

    <p>
        Batting strike rate (s/r) is defined for a batter as the average number of runs scored per 100 balls faced. The higher the strike rate, the more effective a batter is at scoring quickly.
    </p>

    <form method="POST">
        <label for="balls">Balls Faced:</label>
        <input type="number" id="balls" name="balls" required><br>

        <label for="runs">Runs Scored:</label>
        <input type="number" id="runs" name="runs" required><br>

        <input type="submit" value="Calculate">
    </form>

    <p><a href="../index.php">Go to homepage</a></p><br>

    <!-- Include Trivia -->
    <?php include '../trivia.php'; ?>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve values from form submission
        $balls = $_POST["balls"];
        $runs = $_POST["runs"];

        // Check if both values are provided
        if (!empty($balls) && !empty($runs)) {
            // Calculate strike rate
            $strikeRate = ($runs / $balls) * 100;
            echo "<p>Strike Rate: $strikeRate</p>";
        } else {
            echo "<p>Please fill in both fields.</p>";
        }
    }
    ?>
</body>

</html>