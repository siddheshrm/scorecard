<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DLS Par Score Calculator</title>
    <link rel="icon" href="../media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="../css/common.css">
</head>

<body>
    <h2>DLS Par Score Calculator</h2>

    <p>
        The Duckworth–Lewis–Stern (D/L/S) method is a mathematical formula used to calculate a revised target score for the team batting second in a limited-overs cricket match that has been interrupted by weather or other circumstances.
        It was developed in the 1990s to replace other rules for dealing with rain during cricket games. The method ensures a fair outcome by accounting for the number of overs and wickets remaining in the interrupted match.
    </p>
    <hr>

    <form action="dls_calculator.php" method="post">
        <!-- DLS Scenarios -->
        <label for="matchSituation">Match Situation To Determine Par Score</label>
        <select id="matchSituation" name="matchSituation" required>
            <option value="teamAInterrupted">Team batting first could not complete 50 overs</option>
            <option value="teamBPartiallyPlayed">Team batting second played some overs, but could not complete 50 overs</option>
            <option value="teamBNotStarted">Inning 2 reduced by some overs</option>
        </select><br>
        <hr>

        <!-- Team Batting First -->
        <label>Team Batting First</label>
        <label for="teamARuns">Runs Scored:</label>
        <input type="number" id="teamARuns" name="teamARuns" value="<?php if (isset($_POST['teamARuns'])) echo $_POST['teamARuns']; ?>" required><br>

        <label for="teamAWickets">Wickets Lost:</label>
        <input type="number" id="teamAWickets" name="teamAWickets" min="0" max="10" value="<?php if (isset($_POST['teamAWickets'])) echo $_POST['teamAWickets']; ?>" required><br>

        <label for="teamAOvers">Overs Completed:</label>
        <input type="number" id="teamAOvers" name="teamAOvers" min="0" max="50" value="<?php if (isset($_POST['teamAOvers'])) echo $_POST['teamAOvers']; ?>" required><br>
        <hr>

        <!-- Team Batting Second -->
        <label>Team Batting Second</label>
        <label for="teamBRuns">Runs Scored:</label>
        <input type="number" id="teamBRuns" name="teamBRuns" value="<?php if (isset($_POST['teamBRuns'])) echo $_POST['teamBRuns']; ?>"><br>

        <label for="teamBWickets">Wickets Lost:</label>
        <input type="number" id="teamBWickets" name="teamBWickets" min="0" max="10" value="<?php if (isset($_POST['teamBWickets'])) echo $_POST['teamBWickets']; ?>"><br>

        <label for="teamBOvers">Overs Completed:</label>
        <input type="number" id="teamBOvers" name="teamBOvers" min="0" max="50" value="<?php if (isset($_POST['teamBOvers'])) echo $_POST['teamBOvers']; ?>"><br>
        <hr>

        <!-- Team Batting Second Inning Reduced -->
        <label for="teamBOversReduced">Inning 2 Reduced To Overs:</label>
        <input type="number" id="teamBOversReduced" name="teamBOversReduced" min="0" max="50" value="<?php if (isset($_POST['teamBOversReduced'])) echo $_POST['teamBOversReduced']; ?>"><br>

        <input type="submit" value="Calculate Par Score">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include '../config.php';

        // Retrieve inputs from the form and ensure they are treated as numbers
        $teamARuns = (int)$_POST['teamARuns'];
        $teamAWickets = (int)$_POST['teamAWickets'];
        $teamAOvers = (int)$_POST['teamAOvers'];
        // Initialize Team B variables (default 0 if not provided)
        $teamBRuns = isset($_POST['teamBRuns']) ? (int)$_POST['teamBRuns'] : 0;
        $teamBWickets = isset($_POST['teamBWickets']) ? (int)$_POST['teamBWickets'] : 0;
        $teamBOvers = isset($_POST['teamBOvers']) ? (int)$_POST['teamBOvers'] : 0;
        $teamBOversReduced = isset($_POST['teamBOversReduced']) ? (int)$_POST['teamBOversReduced'] : 0;

        // Calculate overs left
        $oversALeft = 50 - $teamAOvers;
        $oversBLeft = 50 - $teamBOvers;

        // Determine the match situation selected in the form
        $matchSituation = $_POST['matchSituation'];

        // Adjust par score calculation based on match situation
        switch ($matchSituation) {
            case 'teamAInterrupted':
                $query = "SELECT wickets_lost_$teamAWickets AS resources_percentage_remain FROM dls_calculation WHERE overs_left = $oversALeft";
                break;

            case 'teamBPartiallyPlayed':
                $query = "SELECT wickets_lost_$teamBWickets AS resources_percentage_remain FROM dls_calculation WHERE overs_left = $oversBLeft";
                break;

            case 'teamBNotStarted':
                $query = "SELECT wickets_lost_$teamBWickets AS resources_percentage_remain FROM dls_calculation WHERE overs_left = $teamBOversReduced";
                break;

            default:
                echo "Error: Invalid match situation selected.";
                exit;
        }

        if (isset($query)) {
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $resourcesPercentageRemain = (float)$row['resources_percentage_remain'];
                $resourcesPercentageUsed = 100 - $resourcesPercentageRemain;
            } else {
                echo "Error: Could not retrieve resources percentage used.";
                exit;
            }
        }

        switch ($matchSituation) {
            case 'teamAInterrupted':
                $teamBParScore = ceil((($resourcesPercentageUsed * $teamARuns / 100) + $teamARuns) + 1);
                echo "Team batting first could not play 50 overs and match was reduced to overs " . $teamAOvers . " each<br>";
                echo "DLS Par Score for Team B: " . $teamBParScore . " runs in " . $teamAOvers . " overs";
                break;

            case 'teamBPartiallyPlayed':
                $teamBParScore = ceil(($resourcesPercentageRemain / 100) * $teamARuns + 1);
                echo "Team batting second could not play 50 overs and match was abandoned after " . $teamBOvers . " overs<br>";
                echo "DLS Par Score for Team B: " . $teamBParScore . " runs in " . $teamBOvers . " overs";
                break;

            case 'teamBNotStarted':
                $teamBParScore = ceil(($resourcesPercentageRemain / 100) * $teamARuns + 1);
                echo ("Inning two reduced to " . $teamBOversReduced . " overs<br>");
                echo "DLS Par Score for Team B: " . $teamBParScore . " runs in " . $teamBOversReduced . " overs";
                break;

            default:
                echo "Error: Invalid match situation selected.";
                exit;
        }
        mysqli_close($conn);
    }
    ?>

    <p><a href="../index.php">Go to homepage</a></p><br>

    <!-- Include Trivia -->
    <?php include '../trivia/trivia.php'; ?>

</body>

</html>