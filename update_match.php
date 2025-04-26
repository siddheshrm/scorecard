<?php
include 'session_handler.php';
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Match</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tuffy:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/logos/IPL.png" type="image/png">
    <link rel="stylesheet" href="css/update_match.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var inning1Runs = document.getElementById('inning1_runs');
            var inning1Wickets = document.getElementById('inning1_wickets');
            var inning2Runs = document.getElementById('inning2_runs');
            var inning2Wickets = document.getElementById('inning2_wickets');

            var inning1Overs = document.getElementById('inning1_overs');
            var inning1Balls = document.getElementById('inning1_balls');
            var inning2Overs = document.getElementById('inning2_overs');
            var inning2Balls = document.getElementById('inning2_balls');

            // Validation on form submit
            document.querySelector('form').addEventListener('submit', function (event) {
                var runs1 = parseInt(inning1Runs.value);
                var wickets1 = parseInt(inning1Wickets.value);
                var runs2 = parseInt(inning2Runs.value);
                var wickets2 = parseInt(inning2Wickets.value);

                var overs1 = parseInt(inning1Overs.value);
                var balls1 = parseInt(inning1Balls.value);
                var overs2 = parseInt(inning2Overs.value);
                var balls2 = parseInt(inning2Balls.value);

                // Validate inning 2 runs and wickets
                if (runs2 > runs1) {
                    if (wickets2 === 10) {
                        alert('Team batting second cannot lose all 10 wickets to win.');
                        event.preventDefault();
                        return;
                    }
                    if (runs2 > runs1 + 6) {
                        alert('Team batting second cannot score more than 6 runs to win.');
                        event.preventDefault();
                        return;
                    }
                }

                // Validate inning 1 and inning 2 balls when overs played is 20
                if ((overs1 === 20 && balls1 !== 0) || (overs2 === 20 && balls2 !== 0)) {
                    alert('If overs played is 20, balls played must be 0.');
                    event.preventDefault();
                    return;
                }

                // Confirmation alert if runs scored is zero for either inning
                if (runs1 === 0 || runs2 === 0) {
                    var confirmZeroRuns = confirm('The runs scored for one or both innings are zero. This will heavily impact the Net Run Rate for that particular team. Are you sure you want to continue with zero runs?');
                    if (!confirmZeroRuns) {
                        event.preventDefault();
                        return;
                    }
                }
            });
        });
    </script>

</head>

<body>
    <h2>Update Match</h2>

    <?php
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
            echo "<h3>$home_team vs $away_team</h3>";
            $formatted_date = date("F j, Y", strtotime($row['date']));
            echo "<p>Date: $formatted_date</p>";
            echo "<p>Venue: $venue</p>";
            echo "<p>$toss_team won the toss and decided to $decision first</p>";
            echo "</div>";

            // Display innings details with input fields
            echo "<div class='innings'>";

            echo "<form id='matchForm' action='submit_score.php' method='post'>";
            echo "<input type='hidden' name='match_no' value='$match_no'>";

            echo "<label for='match_status'>Match Status</label>";
            echo "<select name='match_status' id='match_status' required onchange='toggleMatchStatus(this.value)'>";
            echo "<option value='completed'>Completed</option>";
            echo "<option value='reduced'>Reduced Overs (Shortened Match Due To Rain, etc.)</option>";
            echo "<option value='abandoned'>No Result (Abandoned Due To Rain, Cancelled, etc.)</option>";
            echo "</select>";

            // Hidden flag field
            echo "<input type='hidden' name='isCompleted' id='isCompleted' value='true'>";

            // Reduced overs input (initially hidden)
            echo "<div id='reducedOversInput' style='display:none; margin-top:10px;'>";
            echo "<label for='reduced_match_overs'>Enter Total Overs Per Side (Reduced Match)</label>";
            // As per ICC rules, both teams need to have batted for a minimum of 5 overs each to constitute a valid match in a T20 match
            echo "<input type='number' name='reduced_match_overs' id='reduced_match_overs' placeholder='Enter overs between 5 and 19' min='5' max='19'>";
            echo "</div>";

            echo "<h3>Innings Details</h3>";

            echo "<p>Inning 1: $batting_team</p>";
            echo "<label for='inning1_runs'>Runs Scored:</label>";
            echo "<input type='number' id='inning1_runs' name='inning1_runs' required>";
            echo "<label for='inning1_wickets'>Wickets Lost:</label>";
            echo "<input type='number' id='inning1_wickets' name='inning1_wickets' min='0' max='10' required>";
            echo "<label for='inning1_overs'>Overs Played:</label>";
            echo "<input type='number' id='inning1_overs' name='inning1_overs' min='0' required>";
            echo "<label for='inning1_balls'>Balls Played:</label>";
            echo "<input type='number' id='inning1_balls' name='inning1_balls' min='0' max='5' required>";

            echo "<p>Inning 2: $bowling_team</p>";
            echo "<label for='inning2_runs'>Runs Scored:</label>";
            echo "<input type='number' id='inning2_runs' name='inning2_runs' required>";
            echo "<label for='inning2_wickets'>Wickets Lost:</label>";
            echo "<input type='number' id='inning2_wickets' name='inning2_wickets' min='0' max='10' required>";
            echo "<label for='inning2_overs'>Overs Played:</label>";
            echo "<input type='number' id='inning2_overs' name='inning2_overs' min='0' required>";
            echo "<label for='inning2_balls'>Balls Played:</label>";
            echo "<input type='number' id='inning2_balls' name='inning2_balls' min='0' max='5' required>";

            echo "<div id='superOverSection' style='display:none; margin-top: 10px;'>";
            echo "<p>Match is tied. Please select Super Over winner.</p>";
            echo "<div style='display: flex; gap: 100px;'>";
            echo "<label><input type='radio' name='super_over_winner' value='$batting_team'> $batting_team</label>";
            echo "<label><input type='radio' name='super_over_winner' value='$bowling_team'> $bowling_team</label>";
            echo "</div>";
            echo "</div>";

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

    <p><a href="view_matches.php">See All Matches</a> | <a href="admin_dashboard.php">Go To Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>

    <script>
        function toggleMatchStatus(value) {
            const form = document.getElementById('matchForm');
            const isCompletedField = document.getElementById('isCompleted');
            const reducedInputDiv = document.getElementById('reducedOversInput');
            const reducedInput = document.getElementById('reduced_match_overs');

            // Exclude reduced_overs field from disabling
            const scoreInputs = Array.from(document.querySelectorAll("input[type='number']")).filter(
                input => input.id !== 'reduced_match_overs'
            );

            if (value === 'abandoned') {
                isCompletedField.value = 'false';
                form.action = 'handle_abandoned_match.php';
                scoreInputs.forEach(input => input.disabled = true);
                reducedInputDiv.style.display = 'none';
                reducedInput.required = false;
                reducedInput.disabled = true;
            } else if (value === 'reduced') {
                isCompletedField.value = 'true';
                form.action = 'submit_score.php';
                scoreInputs.forEach(input => input.disabled = false);
                reducedInputDiv.style.display = 'block';
                reducedInput.required = true;
                reducedInput.disabled = false;
            } else {
                isCompletedField.value = 'true';
                form.action = 'submit_score.php';
                scoreInputs.forEach(input => input.disabled = false);
                reducedInputDiv.style.display = 'none';
                reducedInput.required = false;
                reducedInput.disabled = true;
            }

            if (value === 'reduced') {
                reducedInput.setCustomValidity('');
                reducedInput.addEventListener('input', function () {
                    if (this.value < 5 || this.value > 19) {
                        this.setCustomValidity('Overs must be between 5 and 19');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        }
    </script>

    <script>
        function checkForTieAndShowSuperOver() {
            // Get inning-wise runs from input fields
            const inning1_runs = parseInt(document.getElementById('inning1_runs').value) || 0;
            const inning2_runs = parseInt(document.getElementById('inning2_runs').value) || 0;
            const superOverSection = document.getElementById('superOverSection');

            // Show super over options if tied and both runs are > 0
            if (inning1_runs > 0 && inning2_runs > 0 && inning1_runs === inning2_runs) {
                superOverSection.style.display = 'block';
            } else {
                superOverSection.style.display = 'none';
            }
        }

        // Keep checking for tied match score on user inputs
        document.getElementById('inning1_runs').addEventListener('input', checkForTieAndShowSuperOver);
        document.getElementById('inning2_runs').addEventListener('input', checkForTieAndShowSuperOver);

        // Validate form before submitting
        document.getElementById('matchForm').addEventListener('submit', function (event) {
            const inning1_runs = parseInt(document.getElementById('inning1_runs').value) || 0;
            const inning2_runs = parseInt(document.getElementById('inning2_runs').value) || 0;
            const matchStatus = document.getElementById('match_status').value;

            const isTied = inning1_runs === inning2_runs && inning1_runs > 0;
            const isValidStatus = matchStatus === 'completed' || matchStatus === 'reduced';

            if (isTied && isValidStatus) {
                const radios = document.getElementsByName('super_over_winner');
                let isSelected = false;
                for (let radio of radios) {
                    if (radio.checked) {
                        isSelected = true;
                        break;
                    }
                }

                if (!isSelected) {
                    alert("Please select a Super Over winner.");
                    event.preventDefault(); // Stop form from submitting
                }
            }
        });
    </script>

    <script>
        const reducedOversInput = document.getElementById('reduced_match_overs');
        const inning1Overs = document.getElementById('inning1_overs');
        const inning1Balls = document.getElementById('inning1_balls');
        const inning2Overs = document.getElementById('inning2_overs');
        const inning2Balls = document.getElementById('inning2_balls');

        function handleOversValidation() {
            const matchOvers = parseInt(reducedOversInput.value) || 20; // Default 20 if not reduced
            inning1Overs.max = matchOvers;
            inning2Overs.max = matchOvers;

            // Inning-1
            if (parseInt(inning1Overs.value) === matchOvers) {
                inning1Balls.value = 0;
                inning1Balls.readOnly = true;
            } else {
                inning1Balls.readOnly = false;
            }

            // Inning-2
            if (parseInt(inning2Overs.value) === matchOvers) {
                inning2Balls.value = 0;
                inning2Balls.readOnly = true;
            } else {
                inning2Balls.readOnly = false;
            }
        }

        // Attach listeners to validate when overs change
        reducedOversInput.addEventListener('input', handleOversValidation);
        inning1Overs.addEventListener('input', handleOversValidation);
        inning2Overs.addEventListener('input', handleOversValidation);
    </script>
</body>

</html>