<?php
include 'config.php';

// Check if match_no parameter is provided
if (isset($_GET['match_no']) && !empty($_GET['match_no'])) {
    // Sanitize the match_no parameter to prevent SQL injection
    $match_no = mysqli_real_escape_string($conn, $_GET['match_no']);

    // Fetch the match details before deleting
    $sql_fetch_match = "SELECT home_team, away_team, winning_team, losing_team, nrr, result FROM tournament_data WHERE match_no = '$match_no'";
    $result_fetch_match = $conn->query($sql_fetch_match);

    if ($result_fetch_match->num_rows > 0) {
        $row = $result_fetch_match->fetch_assoc();
        $home_team = $row['home_team'];
        $away_team = $row['away_team'];
        $winning_team = $row['winning_team'];
        $losing_team = $row['losing_team'];
        $nrr = $row['nrr'];
        $result = $row['result'];

        // Prepare a delete statement
        $sql_delete_match = "DELETE FROM tournament_data WHERE match_no = '$match_no'";

        // Execute the delete statement
        if ($conn->query($sql_delete_match) === TRUE) {
            if ($result !== NULL) {
                if ($winning_team !== NULL && $losing_team !== NULL) {
                    // Fetch the short_name for the winning team
                    $sql_fetch_winning_team_short_name = "SELECT short_name FROM teams WHERE team_name = '$winning_team'";
                    $result_fetch_winning_team_short_name = $conn->query($sql_fetch_winning_team_short_name);

                    if ($result_fetch_winning_team_short_name->num_rows > 0) {
                        $winning_team_row = $result_fetch_winning_team_short_name->fetch_assoc();
                        $winning_team_short_name = $winning_team_row['short_name'];

                        // Determine the losing team
                        $losing_team = ($home_team === $winning_team) ? $away_team : $home_team;

                        // Update wins and points for the winning team
                        $update_winning_team = "UPDATE teams SET wins = wins - 1, points = points - 2, matches_played = matches_played - 1, nrr = nrr - $nrr WHERE short_name = '$winning_team_short_name'";
                        if ($conn->query($update_winning_team) !== TRUE) {
                            echo "Error updating wins and points for winning team: " . $conn->error;
                        }

                        // Update losses for the losing team
                        $update_losing_team = "UPDATE teams SET losses = losses - 1, matches_played = matches_played - 1, nrr = nrr + $nrr WHERE short_name = '$losing_team'";
                        if ($conn->query($update_losing_team) !== TRUE) {
                            echo "Error updating losses for losing team: " . $conn->error;
                        }
                    } else {
                        echo "Winning team not found in teams table.";
                    }
                } elseif ($winning_team == NULL && $losing_team == NULL) {
                    // Update no_result and points for both teams involved in the tie
                    $update_tied_team_one = "UPDATE teams SET no_result = no_result - 1, points = points - 1, matches_played = matches_played - 1 WHERE short_name = '$home_team'";
                    if ($conn->query($update_tied_team_one) !== TRUE) {
                        echo "Error updating matches played and points for tied team 1: " . $conn->error;
                    }

                    $update_tied_team_two = "UPDATE teams SET no_result = no_result - 1, points = points - 1, matches_played = matches_played - 1 WHERE short_name = '$away_team'";
                    if ($conn->query($update_tied_team_two) !== TRUE) {
                        echo "Error updating matches played and points for tied team 2: " . $conn->error;
                    }
                }
            }

            // Redirect to view_matches.php after successful deletion and update
            header("Location: view_matches.php");
            exit();
        } else {
            // If deletion fails, display an error message
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Match not found.";
    }
} else {
    // If match_no parameter is not provided, redirect to view_matches.php
    header("Location: view_matches.php");
    exit();
}
