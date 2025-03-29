<?php
include 'session_handler.php';
include 'config.php';

// Check if match_no parameter is provided
if (isset($_GET['match_no']) && !empty($_GET['match_no'])) {
    // Sanitize the match_no parameter to prevent SQL injection
    $match_no = mysqli_real_escape_string($conn, $_GET['match_no']);

    // Fetch the match details before deleting
    $sql_fetch_match = "SELECT * FROM tournament_data WHERE match_no = '$match_no'";
    $result_fetch_match = $conn->query($sql_fetch_match);

    if ($result_fetch_match->num_rows > 0) {
        $row = $result_fetch_match->fetch_assoc();
        $home_team = $row['home_team'];
        $away_team = $row['away_team'];
        $winning_team = $row['winning_team'];
        $losing_team = $row['losing_team'];
        $result = $row['result'];
        $toss = $row['toss'];
        $decision = $row['decision'];
        $runs_scored_home = $row['runs_scored_home'];
        $overs_played_home_nrr = $row['overs_played_home_nrr'];
        $balls_played_home_nrr = $row['balls_played_home_nrr'];
        $runs_scored_away = $row['runs_scored_away'];
        $overs_played_away_nrr = $row['overs_played_away_nrr'];
        $balls_played_away_nrr = $row['balls_played_away_nrr'];

        // Home team won the toss and decided to bowl first; away team bats first
        if (($home_team === $toss) && ($decision === 'bowl')) {
            // Away team bats first, Home team bowls first
            $batting_first_team = $away_team;
            $batting_second_team = $home_team;

            $inning1_runs = $row['runs_scored_away'];
            $inning1_overs_nrr = $row['overs_played_away_nrr'];
            $inning1_balls_nrr = $row['balls_played_away_nrr'];

            $inning2_runs = $row['runs_scored_home'];
            $inning2_overs_nrr = $row['overs_played_home_nrr'];
            $inning2_balls_nrr = $row['balls_played_home_nrr'];
        } else {
            // Home team bats first, Away team bowls first
            $batting_first_team = $home_team;
            $batting_second_team = $away_team;

            $inning1_runs = $row['runs_scored_home'];
            $inning1_overs_nrr = $row['overs_played_home_nrr'];
            $inning1_balls_nrr = $row['balls_played_home_nrr'];

            $inning2_runs = $row['runs_scored_away'];
            $inning2_overs_nrr = $row['overs_played_away_nrr'];
            $inning2_balls_nrr = $row['balls_played_away_nrr'];
        }

        // Update statistics for both teams based on Home and Away team
        $stmt_update_stats = $conn->prepare("UPDATE teams SET runs_scored = runs_scored - ?, overs_played = overs_played - ?, balls_played = balls_played - ?, runs_conceded = runs_conceded - ?, overs_bowled = overs_bowled - ?, balls_bowled = balls_bowled - ?, matches_played = matches_played - 1 WHERE short_name = ?");

        // Update the team that batted first
        $stmt_update_stats->bind_param("iiiiiis", $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $batting_first_team);
        $stmt_update_stats->execute();

        // Update the team that batted second
        $stmt_update_stats->bind_param("iiiiiis", $inning2_runs, $inning2_overs_nrr, $inning2_balls_nrr, $inning1_runs, $inning1_overs_nrr, $inning1_balls_nrr, $batting_second_team);
        $stmt_update_stats->execute();

        $stmt_update_stats->close();

        // Fetch short_name of winning and losing teams
        // Fetch short_name of winning team
        $sql_fetch_winning_team = "SELECT short_name FROM teams WHERE team_name = '$winning_team'";
        $result_fetch_winning_team = $conn->query($sql_fetch_winning_team);

        if ($result_fetch_winning_team->num_rows > 0) {
            $row = $result_fetch_winning_team->fetch_assoc();
            $winning_team_short = $row['short_name'];
        }

        // Fetch short_name of losing team
        $sql_fetch_losing_team = "SELECT short_name FROM teams WHERE team_name = '$losing_team'";
        $result_fetch_losing_team = $conn->query($sql_fetch_losing_team);

        if ($result_fetch_losing_team->num_rows > 0) {
            $row = $result_fetch_losing_team->fetch_assoc();
            $losing_team_short = $row['short_name'];
        }

        // Adjust win/loss/tie records based on match result
        if ($result !== NULL) {
            if ($winning_team !== NULL && $losing_team !== NULL) {
                // Update wins and points for the winning team
                $update_winning_team = "UPDATE teams SET wins = wins - 1, points = points - 2 WHERE short_name = '$winning_team_short'";
                $conn->query($update_winning_team);

                // Update losses for the losing team
                $update_losing_team = "UPDATE teams SET losses = losses - 1 WHERE short_name = '$losing_team_short'";
                $conn->query($update_losing_team);
            } elseif ($winning_team == NULL && $losing_team == NULL) {
                // Update no_result and points for tied teams
                $update_tied_team_one = "UPDATE teams SET no_result = no_result - 1, points = points - 1 WHERE short_name = '$home_team'";
                $conn->query($update_tied_team_one);

                $update_tied_team_two = "UPDATE teams SET no_result = no_result - 1, points = points - 1 WHERE short_name = '$away_team'";
                $conn->query($update_tied_team_two);
            }
        }

        // Prepare a delete statement
        $sql_delete_match = "DELETE FROM tournament_data WHERE match_no = '$match_no'";

        if ($conn->query($sql_delete_match) === TRUE) {
            // Recalculate NRR for both teams
            $stmt_update_nrr = $conn->prepare("UPDATE teams SET nrr = (runs_scored / (overs_played + (balls_played / 6.0))) - (runs_conceded / (overs_bowled + (balls_bowled / 6.0))) WHERE short_name = ? OR short_name = ?");
            $stmt_update_nrr->bind_param("ss", $home_team, $away_team);
            $stmt_update_nrr->execute();
            $stmt_update_nrr->close();

            // Redirect to view_matches.php after successful deletion
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
