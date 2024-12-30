<?php
if (!isset($conn)) {
    include 'config.php';
}

// Query to fetch points table data
$query = "SELECT team_name, 
                 SUM(wins + losses + no_result) AS matches_played,
                 wins AS wins, 
                 losses AS losses, 
                 no_result AS no_result, 
                 points AS points,
                 nrr AS nrr
          FROM teams
          GROUP BY team_name
          ORDER BY points DESC, nrr DESC, wins DESC";

$result = $conn->query($query);

$points_table = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $points_table[] = $row;
    }
}
?>

<h3>IPL 2024 Points Table</h3>
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