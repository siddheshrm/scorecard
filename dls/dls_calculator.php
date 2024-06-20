<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duckworth-Lewis Stern Calculation</title>
</head>

<body>
    <h2>Duckworth-Lewis Stern Calculation</h2>
    <form action="dls_calculator.php" method="post">
        <label for="teamARuns">Team A Runs Scored:</label>
        <input type="number" id="teamARuns" name="teamARuns" value="<?php if (isset($_POST['teamARuns'])) echo $_POST['teamARuns']; ?>" required><br>

        <label for="teamAWickets">Team A Wickets Lost:</label>
        <input type="number" id="teamAWickets" name="teamAWickets" min="0" max="10" value="<?php if (isset($_POST['teamAWickets'])) echo $_POST['teamAWickets']; ?>" required><br>

        <label for="teamAOvers">Team A Overs Completed:</label>
        <input type="number" id="teamAOvers" name="teamAOvers" min="0" max="50" value="<?php if (isset($_POST['teamAOvers'])) echo $_POST['teamAOvers']; ?>" required><br><br>

        <label for="teamBRuns">Team B Runs Scored:</label>
        <input type="number" id="teamBRuns" name="teamBRuns" value="<?php if (isset($_POST['teamBRuns'])) echo $_POST['teamBRuns']; ?>"><br>

        <label for="teamBWickets">Team B Wickets Lost:</label>
        <input type="number" id="teamBWickets" name="teamBWickets" min="0" max="10" value="<?php if (isset($_POST['teamBWickets'])) echo $_POST['teamBWickets']; ?>"><br>

        <label for="teamBOvers">Team B Overs Completed:</label>
        <input type="number" id="teamBOvers" name="teamBOvers" min="0" max="50" value="<?php if (isset($_POST['teamBOvers'])) echo $_POST['teamBOvers']; ?>"><br><br>

        <input type="submit" value="Calculate">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include '../config.php';

        // Retrieve inputs from the form and ensure they are treated as numbers
        $teamARuns = (int)$_POST['teamARuns'];
        $teamAWickets = (int)$_POST['teamAWickets'];
        $teamAOvers = (int)$_POST['teamAOvers'];
        $teamBRuns = isset($_POST['teamBRuns']) ? (int)$_POST['teamBRuns'] : null;
        $teamBWickets = isset($_POST['teamBWickets']) ? (int)$_POST['teamBWickets'] : null;

        // Calculate overs left for Team A
        $oversLeft = 50 - $teamAOvers;

        // Query to get the resources percentage used by Team A
        $query = "SELECT wickets_lost_$teamAWickets AS resources_percentage_used
                  FROM dls_calculation
                  WHERE overs_left = $oversLeft";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $resourcesPercentageUsed = (float)$row['resources_percentage_used'];
            $resourcesPercentageRemain = 100 - $resourcesPercentageUsed;

            // Calculate Team B's par score
            $teamBParScore = round($teamARuns * ($resourcesPercentageRemain / 100));

            if ($teamBRuns !== null && $teamBWickets !== null) {
                // When Team B has played some overs and lost wickets
                $runsRemaining = $teamBParScore - $teamBRuns;
                $wicketsInHand = 10 - $teamBWickets;

                // Display the result
                echo "<h3>Duckworth-Lewis Stern Result</h3>";
                echo "<p>Team A: $teamARuns/$teamAWickets in $teamAOvers overs</p>";
                echo "<p>Team B: Par Score: $teamBParScore, Runs Remaining: $runsRemaining, Wickets in Hand: $wicketsInHand</p>";
            } else {
                // When Team B has not played yet
                echo "<h3>Duckworth-Lewis Stern Result</h3>";
                echo "<p>Team A: $teamARuns/$teamAWickets in $teamAOvers overs</p>";
                echo "<p>Team B: Par score: $teamBParScore in $teamAOvers overs</p>";
            }
        } else {
            echo "<p>Error fetching data from the database.</p>";
        }

        // Close the database connection
        mysqli_close($conn);
    }
    ?>

</body>

</html>