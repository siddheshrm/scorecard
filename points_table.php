<?php
if (!isset($conn)) {
    include 'config.php';
}

// Query to fetch points table data
$query = "SELECT team_name,
                 logo,
                 SUM(wins + losses + no_result) AS matches_played,
                 wins AS wins, 
                 losses AS losses, 
                 no_result AS no_result, 
                 points AS points,
                 nrr AS nrr,
                 runs_scored as runs_scored,
                 runs_conceded as runs_conceded,
                 (overs_played + (balls_played DIV 6)) + (MOD(balls_played, 6) / 10) AS overs_played,
                 (overs_bowled + (balls_bowled DIV 6)) + (MOD(balls_bowled, 6) / 10) AS overs_bowled
          FROM teams
          GROUP BY team_name
          ORDER BY points DESC, wins DESC, nrr DESC";

$result = $conn->query($query);

// Fetching data into an array
$teams = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }
}
?>

<h2>Indian Premier League 2026</h2>
<table>
    <tr>
        <th>#</th>
        <th>Team</th>
        <th>Matches</th>
        <th>Won</th>
        <th>Lost</th>
        <th>Tied/NR</th>
        <th>For</th>
        <th>Against</th>
        <th>NRR</th>
        <th>Points</th>
        <th></th>
    </tr>

    <?php
    // Mapping CSS classes to teams based on team_name
    function formatTeamClass($name)
    {
        $map = [
            "Chennai Super Kings" => "csk",
            "Mumbai Indians" => "mi",
            "Royal Challengers Bangalore" => "rcb",
            "Kolkata Knight Riders" => "kkr",
            "Rajasthan Royals" => "rr",
            "Sunrisers Hyderabad" => "srh",
            "Delhi Capitals" => "dc",
            "Lucknow Supergiants" => "lsg",
            "Gujarat Titans" => "gt",
            "Punjab Kings" => "pbks"
        ];

        return $map[$name] ?? 'default-team';
    }

    $position = 1;

    // Loop through teams
    foreach ($teams as $team) {
        // Checks if team is positioned within top-4
        $row_class = $position <= 4 ? 'top-team' : '';

        // Assigns CSS class based on team_name
        $team_css_class = formatTeamClass($team['team_name']);

        // 'team-row' is used to handle row click (opens team modal)
        echo "<tr class='team-row $team_css_class $row_class'>";
        echo "<td>" . $position . "</td>";
        echo "<td class='team-info'><img src='" . $team['logo'] . "' alt='" . $team['team_name'] . "'>" . $team['team_name'] . "</td>";
        echo "<td>" . $team['matches_played'] . "</td>";
        echo "<td>" . $team['wins'] . "</td>";
        echo "<td>" . $team['losses'] . "</td>";
        echo "<td>" . $team['no_result'] . "</td>";
        echo "<td>" . $team['runs_scored'] . "/" . number_format($team['overs_played'], 1) . "</td>";
        echo "<td>" . $team['runs_conceded'] . "/" . number_format($team['overs_bowled'], 1) . "</td>";
        $nrr_display = ($team['nrr'] > 0) ? '+' . $team['nrr'] : $team['nrr'];
        echo "<td>" . $nrr_display . "</td>";
        echo "<td>" . $team['points'] . "</td>";

        // Embed team_name dynamically into the data-team attribute for JS access
        echo "<td><i class='fa-solid fa-caret-down dropdown-icon' data-team='" . $team['team_name'] . "'></i></td>";
        echo "</tr>";
        $position++;
    }
    ?>
</table>