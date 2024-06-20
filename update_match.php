<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Scorecard</title>
    <link rel="stylesheet" href="css/update.css">

    <script>
        function updateBallsBowled(oversInputId, ballsInputId) {
            var oversPlayed = document.getElementById(oversInputId).value;
            var ballsBowled = document.getElementById(ballsInputId);
            if (oversPlayed == 20) {
                ballsBowled.value = 0;
                ballsBowled.disabled = true;
            } else {
                ballsBowled.disabled = false;
            }
        }
    </script>
</head>

<body>
    <h2>Update Scorecard</h2>

    <?php
    include 'config.php';

    // Check if match_no parameter is provided
    if (isset($_GET['match_no']) && !empty($_GET['match_no'])) {
        // Sanitize the match_no parameter to prevent SQL injection
        $match_no = intval($_GET['match_no']);

        // Prepare a select statement to fetch match details
        $stmt = $conn->prepare("SELECT date, venue, home_team, away_team, toss, decision FROM tournament_data WHERE match_no = ?");
        $stmt->bind_param("i", $match_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch match details
            $row = $result->fetch_assoc();
            $date = $row['date'];
            $venue = $row['venue'];
            $home_team_short = $row['home_team'];
            $away_team_short = $row['away_team'];
            $toss_short = $row['toss'];
            $decision = $row['decision'];

            // Fetch team names
            $stmt = $conn->prepare("SELECT team_name FROM teams WHERE short_name = ?");

            $stmt->bind_param("s", $home_team_short);
            $stmt->execute();
            $home_team_result = $stmt->get_result();
            $home_team = $home_team_result->fetch_assoc()['team_name'];

            $stmt->bind_param("s", $away_team_short);
            $stmt->execute();
            $away_team_result = $stmt->get_result();
            $away_team = $away_team_result->fetch_assoc()['team_name'];

            $stmt->bind_param("s", $toss_short);
            $stmt->execute();
            $toss_result = $stmt->get_result();
            $toss_team = $toss_result->fetch_assoc()['team_name'];

            // Determine the batting team for inning 1
            if ($decision === 'bowl') {
                $batting_team_short = ($toss_short === $home_team_short) ? $away_team_short : $home_team_short;
                $bowling_team_short = $toss_short;
            } else {
                $batting_team_short = $toss_short;
                $bowling_team_short = ($toss_short === $home_team_short) ? $away_team_short : $home_team_short;
            }

            $stmt->bind_param("s", $batting_team_short);
            $stmt->execute();
            $batting_team_result = $stmt->get_result();
            $batting_team = $batting_team_result->fetch_assoc()['team_name'];

            $stmt->bind_param("s", $bowling_team_short);
            $stmt->execute();
            $bowling_team_result = $stmt->get_result();
            $bowling_team = $bowling_team_result->fetch_assoc()['team_name'];

            // Display match details as a card
            echo "<div class='card'>";
            echo "<h3>$away_team vs $home_team</h3>";
            $formatted_date = date("F j, Y", strtotime($row['date']));
            echo "<p>Date: $formatted_date</p>";
            echo "<p>Venue: $venue</p>";
            echo "<p>$toss_team won the toss and decided to $decision first</p>";
            echo "</div>";

            // Display innings details with input fields
            echo "<div class='innings'>";
            echo "<h3>Innings Details</h3>";
            echo "<p>Inning 1: $batting_team</p>";
            echo "<form action='submit_match_score.php' method='post'>";
            echo "<input type='hidden' name='match_no' value='$match_no'>";
            echo "<label for='inning1_runs'>Runs Scored:</label>";
            echo "<input type='number' id='inning1_runs' name='inning1_runs' required>";
            echo "<label for='inning1_wickets'>Wickets Lost:</label>";
            echo "<input type='number' id='inning1_wickets' name='inning1_wickets' min='0' max='10' required>";
            echo "<label for='inning1_overs'>Overs Played:</label>";
            echo "<input type='number' id='inning1_overs' name='inning1_overs' min='0' max='20' required onchange='updateBallsBowled(\"inning1_overs\", \"inning1_balls\")'>";
            echo "<label for='inning1_balls'>Balls Played:</label>";
            echo "<input type='number' id='inning1_balls' name='inning1_balls' min='0' max='5' required>";

            echo "<p>Inning 2: $bowling_team</p>";
            echo "<label for='inning2_runs'>Runs Scored:</label>";
            echo "<input type='number' id='inning2_runs' name='inning2_runs' required>";
            echo "<label for='inning2_wickets'>Wickets Lost:</label>";
            echo "<input type='number' id='inning2_wickets' name='inning2_wickets' min='0' max='10' required>";
            echo "<label for='inning2_overs'>Overs Played:</label>";
            echo "<input type='number' id='inning2_overs' name='inning2_overs' min='0' max='20' required onchange='updateBallsBowled(\"inning2_overs\", \"inning2_balls\")'>";
            echo "<label for='inning2_balls'>Balls Played:</label>";
            echo "<input type='number' id='inning2_balls' name='inning2_balls' min='0' max='5' required>";
            echo "<br>";
            echo "<input type='submit' value='Submit Score'>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "No match found with match number $match_no";
        }

        $stmt->close();
    } else {
        echo "Match number not provided";
    }

    $conn->close();
    ?>
</body>

</html>