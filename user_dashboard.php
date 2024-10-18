<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

$username = $_SESSION['username'];

// Query the database to fetch user details based on the username
$sql = "SELECT * FROM users WHERE name = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User found, fetch user details
    $row = $result->fetch_assoc();
    $name = $row['name'];
} else {
    // User not found (This should not happen if user is logged in)
    echo "Error: User not found.";
    exit();
}

// Query the database to fetch points table

$sql_points = "SELECT team_name, 
                SUM(wins + losses + no_result) AS matches_played,
                wins AS wins, 
                losses AS losses, 
                no_result AS no_result, 
                points AS points,
                nrr AS nrr
        FROM teams
        GROUP BY team_name
        ORDER BY points DESC, nrr DESC, wins DESC";

$result_points = $conn->query($sql_points);

$points_table = [];
if ($result_points->num_rows > 0) {
    while ($row = $result_points->fetch_assoc()) {
        $points_table[] = $row;
    }
} else {
    echo "No data found in teams.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" href="./media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="css/table.css">
</head>

<body>
    <h2>Welcome to Dashboard, <?php echo $name; ?>.</h2>

    <h3>Points Table</h3>
    <table>
        <tr>
            <th>Position</th>
            <th>Team</th>
            <th>Matches Played</th>
            <th>Wins</th>
            <th>Losses</th>
            <th>Tie/No Result</th>
            <th>Points</th>
            <th>NRR</th>
        </tr>
        <?php
        $position = 1;
        foreach ($points_table as $team) {
            $row_class = $position <= 4 ? 'top-team' : '';
            echo "<tr class='$row_class'>";
            echo "<td>" . $position . "</td>";
            echo "<td>" . $team['team_name'] . "</td>";
            echo "<td>" . $team['matches_played'] . "</td>";
            echo "<td>" . $team['wins'] . "</td>";
            echo "<td>" . $team['losses'] . "</td>";
            echo "<td>" . $team['no_result'] . "</td>";
            echo "<td>" . $team['points'] . "</td>";
            echo "<td>" . $team['nrr'] . "</td>";
            echo "</tr>";
            $position++;
        }
        ?>
    </table>

    <p><a href="index.php">Logout</a></p>
</body>

</html>