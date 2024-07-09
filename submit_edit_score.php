<?php
include 'config.php';

// Initialize variables for result and redirect URL
$resultMessage = "";
$redirectURL = "view_matches.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all necessary fields are set and not empty
    if (
        isset($_POST['match_no']) && isset($_POST['inning1_runs']) && isset($_POST['inning1_wickets']) &&
        isset($_POST['inning1_overs']) && isset($_POST['inning1_balls']) &&
        isset($_POST['inning2_runs']) && isset($_POST['inning2_wickets']) &&
        isset($_POST['inning2_overs']) && isset($_POST['inning2_balls'])
    ) {
        // Get form data
        $match_no = intval($_POST['match_no']);

        $inning1_runs = intval($_POST['inning1_runs']);
        $inning1_wickets = intval($_POST['inning1_wickets']);
        $inning1_overs = intval($_POST['inning1_overs']);
        $inning1_balls = intval($_POST['inning1_balls']);

        $inning2_runs = intval($_POST['inning2_runs']);
        $inning2_wickets = intval($_POST['inning2_wickets']);
        $inning2_overs = intval($_POST['inning2_overs']);
        $inning2_balls = intval($_POST['inning2_balls']);

        // Calculate overs and balls for NRR based on wickets lost
        if ($inning1_wickets == 10) {
            $inning1_overs_nrr = 20;
            $inning1_balls_nrr = 0;
        } else {
            $inning1_overs_nrr = $inning1_overs;
            $inning1_balls_nrr = $inning1_balls;
        }

        if ($inning2_wickets == 10) {
            $inning2_overs_nrr = 20;
            $inning2_balls_nrr = 0;
        } else {
            $inning2_overs_nrr = $inning2_overs;
            $inning2_balls_nrr = $inning2_balls;
        }

        // Calculate NRR for both teams
        $overs_played_inning1 = $inning1_overs_nrr + ($inning1_balls_nrr / 6);
        $overs_played_inning2 = $inning2_overs_nrr + ($inning2_balls_nrr / 6);

        // Fetch match details to get the teams
        $stmt = $conn->prepare("SELECT home_team, away_team, toss, decision, winning_team, losing_team, nrr FROM tournament_data WHERE match_no = ?");
        $stmt->bind_param("i", $match_no);
        $stmt->execute();
        $result_set = $stmt->get_result();

        if ($result_set->num_rows > 0) {
            $row = $result_set->fetch_assoc();
            $home_team_short = $row['home_team'];
            $away_team_short = $row['away_team'];
            $toss_short = $row['toss'];
            $decision = $row['decision'];
            $existing_nrr = $row['nrr'];
            // Initialize existing result, winning team, and losing team from $row
            $existing_result = isset($row['result']) ? $row['result'] : NULL;
            $existing_winning_team = isset($row['winning_team']) ? $row['winning_team'] : NULL;
            $existing_losing_team = isset($row['losing_team']) ? $row['losing_team'] : NULL;

            // Determine the batting team for inning 1
            if ($decision === 'bowl') {
                $batting_team_short = ($toss_short === $home_team_short) ? $away_team_short : $home_team_short;
                $bowling_team_short = $toss_short;
            } else {
                $batting_team_short = $toss_short;
                $bowling_team_short = ($toss_short === $home_team_short) ? $away_team_short : $home_team_short;
            }

            // Fetch team names based on short names
            $stmt = $conn->prepare("SELECT team_name FROM teams WHERE short_name = ?");

            $stmt->bind_param("s", $batting_team_short);
            $stmt->execute();
            $batting_team_result = $stmt->get_result();
            $batting_team = $batting_team_result->fetch_assoc()['team_name'];

            $stmt->bind_param("s", $bowling_team_short);
            $stmt->execute();
            $bowling_team_result = $stmt->get_result();
            $bowling_team = $bowling_team_result->fetch_assoc()['team_name'];

            // Calculate positive NRR
            $nrr = abs(($inning1_runs / $overs_played_inning1) - ($inning2_runs / $overs_played_inning2));

            // Determine winning and losing teams based on user inputs
            if ($inning1_runs > $inning2_runs) {
                $resultMessage = "$batting_team won by " . ($inning1_runs - $inning2_runs) . " runs";
                $winning_team = $batting_team;
                $losing_team = $bowling_team;
            } elseif ($inning1_runs < $inning2_runs) {
                $resultMessage = "$bowling_team won by " . (10 - $inning2_wickets) . " wickets";
                $winning_team = $bowling_team;
                $losing_team = $batting_team;
            } else {
                $resultMessage = "The match is tied";
                $winning_team = NULL;
                $losing_team = NULL;
            }

            // Initialize short names
            $winning_team_short = NULL;
            $losing_team_short = NULL;

            // Determine short names for winning and losing teams
            if ($winning_team !== NULL) {
                // Fetch short name for winning team
                $stmt = $conn->prepare("SELECT short_name FROM teams WHERE team_name = ?");
                $stmt->bind_param("s", $winning_team);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $winning_team_short = $result->fetch_assoc()['short_name'];
                } else {
                    echo "No short name found for winning team<br>";
                }
                $stmt->close();
            }

            if ($losing_team !== NULL) {
                // Fetch short name for losing team
                $stmt = $conn->prepare("SELECT short_name FROM teams WHERE team_name = ?");
                $stmt->bind_param("s", $losing_team);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $losing_team_short = $result->fetch_assoc()['short_name'];
                } else {
                    echo "No short name found for losing team<br>";
                }
                $stmt->close();
            }

            // Prepare update statement for tournament_data
            $stmt = $conn->prepare("UPDATE tournament_data SET result = ?, winning_team = ?, losing_team = ?, runs_scored_home = ?, runs_scored_away = ?, overs_played_home = ?, overs_played_away = ?, balls_played_home = ?, balls_played_away = ?, wickets_lost_home = ?, wickets_lost_away = ?, overs_played_home_nrr = ?, balls_played_home_nrr = ?, overs_played_away_nrr = ?, balls_played_away_nrr = ?, nrr = ? WHERE match_no = ?");

            if ($batting_team_short == $home_team_short) {
                $stmt->bind_param("sssiiiiiiiiiiiidi", $resultMessage, $winning_team, $losing_team, $inning1_runs, $inning2_runs, $inning1_overs, $inning2_overs, $inning1_balls, $inning2_balls, $inning1_wickets, $inning2_wickets, $inning1_overs_nrr, $inning1_balls_nrr, $inning2_overs_nrr, $inning2_balls_nrr, $nrr, $match_no);
            } else {
                $stmt->bind_param("sssiiiiiiiiiiiidi", $resultMessage, $winning_team, $losing_team, $inning2_runs, $inning1_runs, $inning2_overs, $inning1_overs, $inning2_balls, $inning1_balls, $inning2_wickets, $inning1_wickets, $inning2_overs_nrr, $inning2_balls_nrr, $inning1_overs_nrr, $inning1_balls_nrr, $nrr, $match_no);
            }

            if ($stmt->execute()) {
                if ($winning_team !== NULL && $losing_team !== NULL) {
                    // Compare existing winning and losing teams
                    if ($winning_team == $existing_losing_team && $losing_team == $existing_winning_team) {
                        // Result is overturned, losing team won
                        $stmt_update_wins = $conn->prepare("UPDATE teams SET wins = wins + 1, losses = losses - 1, points = points + 2, nrr = nrr + ? + ? WHERE short_name = ?");
                        $stmt_update_wins->bind_param("dds", $nrr, $existing_nrr, $winning_team_short);
                        $stmt_update_wins->execute();
                        $stmt_update_wins->close();

                        // Result is overturned, winning team lost
                        $stmt_update_losses = $conn->prepare("UPDATE teams SET losses = losses + 1, wins = wins - 1, points = points - 2, nrr = nrr - ? - ? WHERE short_name = ?");
                        $stmt_update_losses->bind_param("dds", $nrr, $existing_nrr, $losing_team_short);
                        $stmt_update_losses->execute();
                        $stmt_update_losses->close();
                    } elseif ($winning_team == $existing_winning_team && $losing_team == $existing_losing_team) {
                        // Winning team is same, just update the NRR
                        $stmt_update_wins = $conn->prepare("UPDATE teams SET nrr = nrr + ? - ? WHERE short_name = ?");
                        $stmt_update_wins->bind_param("dds", $nrr, $existing_nrr, $winning_team_short);
                        $stmt_update_wins->execute();
                        $stmt_update_wins->close();

                        // Losing team is same, just update the NRR
                        $stmt_update_losses = $conn->prepare("UPDATE teams SET nrr = nrr - ? + ? WHERE short_name = ?");
                        $stmt_update_losses->bind_param("dds", $nrr, $existing_nrr, $losing_team_short);
                        $stmt_update_losses->execute();
                        $stmt_update_losses->close();
                    }
                }

                // Output JavaScript for alert and redirect
                echo "<script>
                    alert('Result: $resultMessage');
                    window.location.replace('$redirectURL');
                </script>";
                exit;
            } else {
                echo "Error updating record: " . $conn->error;
            }

            // Close statement
            $stmt->close();
        } else {
            echo "Match not found.";
        }

        $stmt->close();
    } else {
        echo '<script>alert("All form fields are required.")</script>';
    }
}

$conn->close();
