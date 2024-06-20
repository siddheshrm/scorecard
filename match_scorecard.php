<?php
include 'config.php';

// Debugging: Dump received POST data
var_dump($_POST);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $date = $_POST['date'];
    $home_team = $_POST['team1'];
    $away_team = $_POST['team2'];
    $venue = $_POST['venue'];
    $toss_won_by = $_POST['toss_won_by'];
    $decided_to = $_POST['decided_to'];

    // Insert data into tournament_data table
    $sql = "INSERT INTO tournament_data (date, home_team, away_team, venue, toss, decision) 
            VALUES ('$date', '$home_team', '$away_team', '$venue', '$toss_won_by', '$decided_to')";

    if ($conn->query($sql) === TRUE) {
        // Update matches_played for home_team
        $update_home_team = "UPDATE teams SET matches_played = matches_played + 1 WHERE short_name = '$home_team'";
        if ($conn->query($update_home_team) !== TRUE) {
            echo "Error updating matches_played for home team: " . $conn->error;
        }

        // Update matches_played for away_team
        $update_away_team = "UPDATE teams SET matches_played = matches_played + 1 WHERE short_name = '$away_team'";
        if ($conn->query($update_away_team) !== TRUE) {
            echo "Error updating matches_played for away team: " . $conn->error;
        }

        // Redirect to view_matches.php after successful submission and update
        header("Location: view_matches.php");
        exit(); // Stop further execution
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>