<?php
if (!isset($conn)) {
    include 'config.php';
}

$team = isset($_GET['team']) ? $_GET['team'] : '';

if (!empty($team)) {
    // Since $team is full name, fetch short_name
    $query_short = "SELECT short_name FROM teams WHERE team_name = ?";
    $stmt_short = $conn->prepare($query_short);
    $stmt_short->bind_param("s", $team);
    $stmt_short->execute();
    $result_short = $stmt_short->get_result();

    if ($row_short = $result_short->fetch_assoc()) {
        $team_short = $row_short['short_name'];
    }

    // Fetch data based on $team passed
    $query = "SELECT tournament_data.date, tournament_data.venue, 
                         tournament_data.home_team, tournament_data.away_team, 
                         tournament_data.toss, tournament_data.decision, tournament_data.result 
                  FROM tournament_data
                  WHERE tournament_data.home_team = ? OR tournament_data.away_team = ?
                  ORDER BY tournament_data.date DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $team_short, $team_short);
    $stmt->execute();
    $result = $stmt->get_result();

    $matches = [];
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<body>
    <div id="matchModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2><?php echo htmlspecialchars($team); ?></h2>
            <table id="matchDetails">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Opponent</th>
                        <th>Venue</th>
                        <th>Toss</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($matches)): ?>
                        <?php foreach ($matches as $match): ?>
                            <tr>
                                <td><?php echo date("d", strtotime($match['date'])) . "&nbsp;" . date("M", strtotime($match['date'])); ?></td>
                                <td><?php echo ($match['home_team'] === $team_short) ? $match['away_team'] : $match['home_team']; ?></td>
                                <td><?php echo $match['venue']; ?></td>
                                <td><?php
                                    if (empty($match['toss']) || empty($match['decision'])) {
                                        echo "No Toss";
                                    } else {
                                        echo $match['toss'] . " decided to " . $match['decision'] . " first";
                                    }
                                    ?>
                                </td>
                                <td><?php echo $match['result']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No matches found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>