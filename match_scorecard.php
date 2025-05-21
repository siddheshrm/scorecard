<?php
include 'config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $date = $_POST['date'];
    $home_team = $_POST['team1'];
    $away_team = $_POST['team2'];
    $venue = $_POST['venue'];
    $toss_won_by = $_POST['toss_won_by'];
    $decided_to = $_POST['decided_to'];
    $is_evening_match = $_POST['is_evening_match'];
    $toss_status = $_POST['toss_status'];

    if ($toss_status === 'abandoned_before_toss') {
        $resultMessage = "Match abandoned without a toss";

        $sql1 = "INSERT INTO tournament_data (date, is_evening_match, home_team, away_team, venue, result)
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("sissss", $date, $is_evening_match, $home_team, $away_team, $venue, $resultMessage);

        if ($stmt1->execute()) {
            $stmt1->close();

            $sql2 = "UPDATE teams 
                     SET matches_played = matches_played + 1,
                         no_result = no_result + 1,
                         points = points + 1
                     WHERE short_name = ? OR short_name = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("ss", $home_team, $away_team);
            $stmt2->execute();
            $stmt2->close();

            header("Location: view_matches.php");
            exit();
        } else {
            // Log error instead of echo in production
            echo "Error: " . $stmt1->error;
            $stmt1->close();
        }
    } else {
        $sql = "INSERT INTO tournament_data (date, is_evening_match, home_team, away_team, venue, toss, decision) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisssss", $date, $is_evening_match, $home_team, $away_team, $venue, $toss_won_by, $decided_to);

        if ($stmt->execute()) {
            header("Location: view_matches.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
