<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DLS Par Score Calculator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tuffy:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../media/logos/IPL.png" type="image/png">
    <link rel="stylesheet" href="../css/common.css">
</head>

<body>
    <h2>DLS Par Score Calculator</h2>

    <p>
        The Duckworth–Lewis–Stern (D/L/S) method is a mathematical formula used to calculate a revised target score for
        the team batting second in a limited-overs cricket match that has been interrupted by weather or other
        circumstances.
        It was developed in the 1990s to replace other rules for dealing with rain during cricket games. The method
        ensures a fair outcome by accounting for the number of overs and wickets remaining in the interrupted match.
    </p>

    <form action="dls_calculator.php" method="post">
        <!-- DLS Scenarios -->
        <div class="section">
            <label for="matchSituation"><b>Match Situation To Determine Par Score <span
                        style="color: red;">*</span></b></label>
            <select id="matchSituation" name="matchSituation" required>
                <option value="teamAInterrupted">Interruption to Team Batting First</option>
                <option value="teamBPartiallyPlayed">Interruption to Team Batting Second</option>
                <option value="teamBNotStarted">Premature curtailment of Second inning</option>
            </select>
        </div>

        <!-- Team Batting First -->
        <label><b>Team Batting First</b></label>
        <div class="section">
            <div class="form-group">
                <label for="teamARuns">Runs Scored <span style="color: red;">*</span></label>
                <input type="number" id="teamARuns" name="teamARuns"
                    value="<?php if (isset($_POST['teamARuns']))
                        echo $_POST['teamARuns']; ?>" required>
            </div>

            <div class="form-group">
                <label for="teamAWickets">Wickets Lost <span style="color: red;">*</span></label>
                <input type="number" id="teamAWickets" name="teamAWickets" min="0" max="10"
                    value="<?php if (isset($_POST['teamAWickets']))
                        echo $_POST['teamAWickets']; ?>" required>
            </div>

            <div class="form-group">
                <label for="teamAOvers">Overs Completed <span style="color: red;">*</span></label>
                <input type="number" id="teamAOvers" name="teamAOvers" min="0" max="50"
                    value="<?php if (isset($_POST['teamAOvers']))
                        echo $_POST['teamAOvers']; ?>" required>
            </div>
        </div>

        <!-- Team Batting Second -->
        <label><b>Team Batting Second</b></label>
        <div class="section">
            <div class="form-group">
                <label for="teamBRuns">Runs Scored</label>
                <input type="number" id="teamBRuns" name="teamBRuns"
                    value="<?php if (isset($_POST['teamBRuns']))
                        echo $_POST['teamBRuns']; ?>">
            </div>

            <div class="form-group">
                <label for="teamBWickets">Wickets Lost</label>
                <input type="number" id="teamBWickets" name="teamBWickets" min="0" max="10"
                    value="<?php if (isset($_POST['teamBWickets']))
                        echo $_POST['teamBWickets']; ?>">
            </div>

            <div class="form-group">
                <label for="teamBOvers">Overs Completed</label>
                <input type="number" id="teamBOvers" name="teamBOvers" min="0" max="50"
                    value="<?php if (isset($_POST['teamBOvers']))
                        echo $_POST['teamBOvers']; ?>">
            </div>
        </div>

        <!-- Team Batting Second Inning Reduced -->
        <div class="section">
            <label for="teamBOversReduced"><b>Inning 2 Reduced To Overs</b></label>
            <input type="number" id="teamBOversReduced" name="teamBOversReduced" min="0" max="50"
                value="<?php if (isset($_POST['teamBOversReduced']))
                    echo $_POST['teamBOversReduced']; ?>">
        </div>

        <!-- Submit Button -->
        <input type="submit" value="Calculate Par Score">
    </form>
    <hr>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include '../config.php';

        // Retrieve inputs from the form and ensure they are treated as numbers
        $teamARuns = (int) $_POST['teamARuns'];
        $teamAWickets = (int) $_POST['teamAWickets'];
        $teamAOvers = (int) $_POST['teamAOvers'];

        // Initialize Team B variables (default 0 if not provided)
        $teamBRuns = isset($_POST['teamBRuns']) ? (int) $_POST['teamBRuns'] : 0;
        $teamBWickets = isset($_POST['teamBWickets']) ? (int) $_POST['teamBWickets'] : 0;
        $teamBOvers = isset($_POST['teamBOvers']) ? (int) $_POST['teamBOvers'] : 0;
        $teamBOversReduced = isset($_POST['teamBOversReduced']) ? (int) $_POST['teamBOversReduced'] : 0;

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
                $resourcesPercentageRemain = (float) $row['resources_percentage_remain'];
                $resourcesPercentageUsed = 100 - $resourcesPercentageRemain;
            } else {
                echo "Error: Could not retrieve resources percentage used.";
                exit;
            }
        }

        switch ($matchSituation) {
            case 'teamAInterrupted':
                $teamBParScore = ceil((($resourcesPercentageUsed * $teamARuns / 100) + $teamARuns) + 1);
                echo "<b>Team batting first could not play 50 overs and match was reduced to overs " . $teamAOvers . " each.</b><br>";
                echo "<b>DLS Par Score for Team B: " . $teamBParScore . " runs in " . $teamAOvers . " overs.</b>";
                break;

            case 'teamBPartiallyPlayed':
                $teamBParScore = ceil(($resourcesPercentageRemain / 100) * $teamARuns + 1);
                echo "<b>Team batting second could not play 50 overs and match was abandoned after " . $teamBOvers . " overs.</b><br>";
                echo "<b>DLS Par Score for Team B: " . $teamBParScore . " runs in " . $teamBOvers . " overs.</b>";
                break;

            case 'teamBNotStarted':
                $teamBParScore = ceil(($resourcesPercentageRemain / 100) * $teamARuns + 1);
                echo ("<b>Inning two reduced to " . $teamBOversReduced . " overs.</b><br>");
                echo "<b>DLS Par Score for Team B: " . $teamBParScore . " runs in " . $teamBOversReduced . " overs.</b>";
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