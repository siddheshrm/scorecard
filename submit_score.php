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
        if ($inning1_runs == 0) {
            $inning1_overs_nrr = 20;
            $inning1_balls_nrr = 0;
        } else {
            if ($inning1_wickets == 10 || ($inning1_overs != 20 && $inning1_wickets != 10)) {
                // Team retired early due to injury / situation. Full 20 overs assumed for NRR.
                $inning1_overs_nrr = 20;
                $inning1_balls_nrr = 0;
            } else {
                $inning1_overs_nrr = $inning1_overs;
                $inning1_balls_nrr = $inning1_balls;
            }
        }

        if ($inning2_runs == 0) {
            $inning2_overs_nrr = 20;
            $inning2_balls_nrr = 0;
        } else {
            // Handle the case where all runs are scored via no balls and wides
            if ($inning2_overs == 0 && $inning2_balls == 0 && $inning2_runs > 0) {
                // Assign a value representing minimum one ball faced to avoid 'division by zero error' while calculating NRR
                echo "<script>alert('Team batting second won without facing a legal delivery. Minimum 1 ball assumed for NRR calculation.');</script>";
                $inning2_overs_nrr = 0;
                $inning2_balls_nrr = 1;
            } elseif ($inning2_wickets == 10 || ($inning2_runs < $inning1_runs && $inning2_wickets != 10)) {
                // Team retired early due to injury / situation. Full 20 overs assumed for NRR.
                $inning2_overs_nrr = 20;
                $inning2_balls_nrr = 0;
            } else {
                $inning2_overs_nrr = $inning2_overs;
                $inning2_balls_nrr = $inning2_balls;
            }
        }

        // Fetch match details to get the teams
        $stmt = $conn->prepare("SELECT home_team, away_team, toss, decision FROM tournament_data WHERE match_no = ?");
        $stmt->bind_param("i", $match_no);
        $stmt->execute();
        $result_set = $stmt->get_result();

        if ($result_set->num_rows > 0) {
            $row = $result_set->fetch_assoc();
            $home_team_short = $row['home_team'];
            $away_team_short = $row['away_team'];
            $toss_short = $row['toss'];
            $decision = $row['decision'];

            // Determine the batting team for inning 1 based on the toss winner's decision
            // Winner decided to bowl
            if ($decision === 'bowl') {
                // If the home team wins the toss, the away team bats first
                $batting_team_short = ($toss_short === $home_team_short) ? $away_team_short : $home_team_short;
                $bowling_team_short = $toss_short;
            } else {
                // If the home team wins the toss and decision is not 'bowl', the home team bats first
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

            // Determine winning and losing teams
            if ($inning1_runs > $inning2_runs) {
                $resultMessage = "$batting_team won by " . ($inning1_runs - $inning2_runs) . " runs";
                $winning_team = $batting_team;
                $losing_team = $bowling_team;
            } elseif ($inning1_runs < $inning2_runs) {
                $resultMessage = "$bowling_team won by " . (10 - $inning2_wickets) . " wickets";
                $winning_team = $bowling_team;
                $losing_team = $batting_team;
            } else {
                // The match is tied
                if (!empty($_POST['super_over_winner'])) {
                    $super_over_winner = $_POST['super_over_winner'];
                    $winning_team = $super_over_winner;
                    $losing_team = ($super_over_winner === $batting_team) ? $bowling_team : $batting_team;
                    $resultMessage = "Match tied. $super_over_winner won the match in Super Over.";
                }
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
                    // Winning team short_name
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
                    // Losing team short_name
                    $losing_team_short = $result->fetch_assoc()['short_name'];
                } else {
                    echo "No short name found for losing team<br>";
                }
                $stmt->close();
            }

            // Prepare update statement for tournament_data
            $stmt = $conn->prepare("UPDATE tournament_data SET result = ?, winning_team = ?, losing_team = ?, runs_scored_home = ?, runs_scored_away = ?, overs_played_home = ?, overs_played_away = ?, balls_played_home = ?, balls_played_away = ?, wickets_lost_home = ?, wickets_lost_away = ?, overs_played_home_nrr = ?, balls_played_home_nrr = ?, overs_played_away_nrr = ?, balls_played_away_nrr = ? WHERE match_no = ?");

            if ($batting_team_short == $home_team_short) {
                $stmt->bind_param("sssiiiiiiiiiiiii", $resultMessage, $winning_team, $losing_team, $inning1_runs, $inning2_runs, $inning1_overs, $inning2_overs, $inning1_balls, $inning2_balls, $inning1_wickets, $inning2_wickets, $inning1_overs_nrr, $inning1_balls_nrr, $inning2_overs_nrr, $inning2_balls_nrr, $match_no);
            } else {
                $stmt->bind_param("sssiiiiiiiiiiiii", $resultMessage, $winning_team, $losing_team, $inning2_runs, $inning1_runs, $inning2_overs, $inning1_overs, $inning2_balls, $inning1_balls, $inning2_wickets, $inning1_wickets, $inning2_overs_nrr, $inning2_balls_nrr, $inning1_overs_nrr, $inning1_balls_nrr, $match_no);
            }

            if ($stmt->execute()) {
                if ($winning_team !== NULL && $losing_team !== NULL) {
                    // Update wins, points, matches_played for the winning team
                    $stmt_update_wins = $conn->prepare("UPDATE teams SET matches_played = matches_played + 1, wins = wins + 1, points = points + 2 WHERE short_name = ?");
                    $stmt_update_wins->bind_param("s", $winning_team_short);
                    $stmt_update_wins->execute();
                    $stmt_update_wins->close();

                    // Update matches_played, losses for the losing team
                    $stmt_update_losses = $conn->prepare("UPDATE teams SET matches_played = matches_played + 1, losses = losses + 1 WHERE short_name = ?");
                    $stmt_update_losses->bind_param("s", $losing_team_short);
                    $stmt_update_losses->execute();
                    $stmt_update_losses->close();

                    // Update batting and bowling stats for NRR calcultaion of the winning team
                    $stmt_update_winning_stats = $conn->prepare("UPDATE teams SET runs_scored = runs_scored + ?, overs_played = overs_played + ?, balls_played = balls_played + ?, runs_conceded = runs_conceded + ?, overs_bowled = overs_bowled + ?, balls_bowled = balls_bowled + ? WHERE short_name = ?");

                    if ($winning_team_short == $batting_team_short) {
                        $stmt_update_winning_stats->bind_param("iiiiiis", $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $winning_team_short);
                        $stmt_update_winning_stats->execute();
                    } else {
                        $stmt_update_winning_stats->bind_param("iiiiiis", $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $winning_team_short);
                        $stmt_update_winning_stats->execute();
                    }
                    $stmt_update_winning_stats->close();

                    // Update batting and bowling stats for NRR calcultaion of the losing team
                    $stmt_update_losing_stats = $conn->prepare("UPDATE teams SET runs_scored = runs_scored + ?, overs_played = overs_played + ?, balls_played = balls_played + ?, runs_conceded = runs_conceded + ?, overs_bowled = overs_bowled + ?, balls_bowled = balls_bowled + ? WHERE short_name = ?");

                    if ($losing_team_short == $batting_team_short) {
                        $stmt_update_losing_stats->bind_param("iiiiiis", $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $losing_team_short);
                        $stmt_update_losing_stats->execute();
                    } else {
                        $stmt_update_losing_stats->bind_param("iiiiiis", $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $losing_team_short);
                        $stmt_update_losing_stats->execute();
                    }
                    $stmt_update_losing_stats->close();

                    // Update Net Run Rate for each team
                    $stmt_update_nrr = $conn->prepare("UPDATE teams SET nrr = (runs_scored / (overs_played + (balls_played / 6.0))) - (runs_conceded / (overs_bowled + (balls_bowled / 6.0))) WHERE overs_played > 0 AND overs_bowled > 0");
                    $stmt_update_nrr->execute();
                    $stmt_update_nrr->close();
                } elseif ($winning_team == NULL && $losing_team == NULL) {
                    // Update matches_played, no_result, and points for both teams
                    $stmt_update_tie = $conn->prepare("UPDATE teams SET matches_played = matches_played + 1, no_result = no_result + 1, points = points + 1 WHERE short_name = ? OR short_name = ?");
                    $stmt_update_tie->bind_param("ss", $home_team_short, $away_team_short);
                    $stmt_update_tie->execute();
                    $stmt_update_tie->close();

                    // Update batting and bowling stats for both teams
                    $stmt_update_tie_stats = $conn->prepare("UPDATE teams SET runs_scored = runs_scored + ?, overs_played = overs_played + ?, balls_played = balls_played + ?, runs_conceded = runs_conceded + ?, overs_bowled = overs_bowled + ?, balls_bowled = balls_bowled + ? WHERE short_name = ?");

                    // Home team bats first
                    if ($home_team_short == $batting_team_short) {
                        // Update stats for home team
                        $stmt_update_tie_stats->bind_param("iiiiiis", $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $home_team_short);
                        $stmt_update_tie_stats->execute();

                        // Update stats for away team
                        $stmt_update_tie_stats->bind_param("iiiiiis", $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $away_team_short);
                        $stmt_update_tie_stats->execute();
                    } else {
                        // Away team bats first
                        // Update stats for home team
                        $stmt_update_tie_stats->bind_param("iiiiiis", $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $away_team_short);
                        $stmt_update_tie_stats->execute();

                        // Update stats for away team
                        $stmt_update_tie_stats->bind_param("iiiiiis", $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $home_team_short);
                        $stmt_update_tie_stats->execute();
                    }
                    $stmt_update_tie_stats->close();

                    // Update NRR for both teams
                    $stmt_update_nrr = $conn->prepare("UPDATE teams SET nrr = (runs_scored / (overs_played + (balls_played / 6.0))) -(runs_conceded / (overs_bowled + (balls_bowled / 6.0))) WHERE short_name = ? OR short_name = ?");
                    $stmt_update_nrr->bind_param("ss", $home_team_short, $away_team_short);
                    $stmt_update_nrr->execute();
                    $stmt_update_nrr->close();
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
