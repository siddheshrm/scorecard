<?php
include 'config.php';

// Initialize variables for result and redirect URL
$resultMessage = "";
$redirectURL = "view_matches.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['match_no'])) {
        // Get match_no
        $match_no = intval($_POST['match_no']);

        // Fetch match details to get the teams
        $stmt = $conn->prepare("SELECT home_team, away_team FROM tournament_data WHERE match_no = ?");
        $stmt->bind_param("i", $match_no);
        $stmt->execute();
        $result_set = $stmt->get_result();

        if ($result_set->num_rows > 0) {
            $row = $result_set->fetch_assoc();
            $home_team_short = $row['home_team'];
            $away_team_short = $row['away_team'];

            $resultMessage = "Match abandoned - No result";

            // Prepare update statement for tournament_data
            $stmt = $conn->prepare("UPDATE tournament_data SET result = ? WHERE match_no = ?");
            $stmt->bind_param("si", $resultMessage, $match_no);

            if ($stmt->execute()) {
                // Update matches_played, no_result, and points for both teams
                $stmt_update_tie = $conn->prepare("UPDATE teams SET matches_played = matches_played + 1, no_result = no_result + 1, points = points + 1 WHERE short_name = ? OR short_name = ?");
                $stmt_update_tie->bind_param("ss", $home_team_short, $away_team_short);
                $stmt_update_tie->execute();
                $stmt_update_tie->close();

                // Output JavaScript for alert and redirect
                echo "<script>
                        alert('Result: $resultMessage');
                        window.location.replace('$redirectURL');
                    </script>";
                exit;
            } else {
                echo "Error updating record: " . $conn->error;
            }
            $stmt->close();
        } else {
            echo "Match not found.";
        }
    } else {
        echo '<script>alert("Something went wrong. Match not found.")</script>';
    }
}

$conn->close();
