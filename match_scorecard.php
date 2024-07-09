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

    // Insert data into tournament_data table
    $sql = "INSERT INTO tournament_data (date, home_team, away_team, venue, toss, decision) 
            VALUES ('$date', '$home_team', '$away_team', '$venue', '$toss_won_by', '$decided_to')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to view_matches.php after successful submission
        header("Location: view_matches.php");
        exit(); // Stop further execution
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
