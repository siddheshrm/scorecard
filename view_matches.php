<?php
include 'session_handler.php';
include 'config.php';

// Pagination setup
$limit = 8;

// Get team filter from URL (if present), otherwise default to empty
$team_filter = isset($_GET['team']) ? $_GET['team'] : '';

// Total records count for pagination
if (!empty($team_filter)) {
    // Count only those matches where the selected team appears (either as home team or away team)
    $total_sql = "SELECT COUNT(*) AS total FROM tournament_data WHERE home_team = ? OR away_team = ?";
    $stmt_total = $conn->prepare($total_sql);
    $stmt_total->bind_param("ss", $team_filter, $team_filter);
    $stmt_total->execute();
    $total_result = $stmt_total->get_result();
} else {
    // No filter applied, count all records in the table
    $total_sql = "SELECT COUNT(*) AS total FROM tournament_data";
    $total_result = $conn->query($total_sql);
}

$total_row = $total_result->fetch_assoc();
$total_records_fetched = $total_row['total'];

// Total pages
$total_pages = ceil($total_records_fetched / $limit);

// Page safety
// If page value is provided, take the maximum between its value and 1, OR use 1
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;

// Ensure valid pages range
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit; // Offset calculation

// Fetch paginated match records with optional team filtering
if (!empty($team_filter)) {
    // Get matches where the selected team is either home or away
    // Results are sorted by latest date first, and evening matches prioritized
    $sql = "SELECT * FROM tournament_data WHERE home_team = ? OR away_team = ? ORDER BY date DESC, is_evening_match DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $team_filter, $team_filter, $limit, $offset);
} else {
    // No filter, fetch all matches with same sorting and pagination
    $sql = "SELECT * FROM tournament_data ORDER BY date DESC, is_evening_match DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament History</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tuffy:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/logos/IPL.png" type="image/png">
    <link rel="stylesheet" href="css/view_matches.css">
    <script src="https://kit.fontawesome.com/9dd0cb4077.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="header-container">
        <h2>Tournament History</h2>

        <form method="GET" class="filter-form">
            <select name="team" onchange="this.form.submit()">
                <option value="">All Teams</option>
                <option value="CSK" <?= (isset($_GET['team']) && $_GET['team'] == 'CSK') ? 'selected' : '' ?>>Chennai Super Kings</option>
                <option value="DC" <?= (isset($_GET['team']) && $_GET['team'] == 'DC') ? 'selected' : '' ?>>Delhi Capitals</option>
                <option value="GT" <?= (isset($_GET['team']) && $_GET['team'] == 'GT') ? 'selected' : '' ?>>Gujarat Titans</option>
                <option value="KKR" <?= (isset($_GET['team']) && $_GET['team'] == 'KKR') ? 'selected' : '' ?>>Kolkata Knight Riders</option>
                <option value="LSG" <?= (isset($_GET['team']) && $_GET['team'] == 'LSG') ? 'selected' : '' ?>>Lucknow Supergiants</option>
                <option value="MI" <?= (isset($_GET['team']) && $_GET['team'] == 'MI') ? 'selected' : '' ?>>Mumbai Indians</option>
                <option value="PBKS" <?= (isset($_GET['team']) && $_GET['team'] == 'PBKS') ? 'selected' : '' ?>>Punjab Kings</option>
                <option value="RR" <?= (isset($_GET['team']) && $_GET['team'] == 'RR') ? 'selected' : '' ?>>Rajasthan Royals</option>
                <option value="RCB" <?= (isset($_GET['team']) && $_GET['team'] == 'RCB') ? 'selected' : '' ?>>Royal Challengers Bangalore</option>
                <option value="SRH" <?= (isset($_GET['team']) && $_GET['team'] == 'SRH') ? 'selected' : '' ?>>Sunrisers Hyderabad</option>
            </select>
        </form>
    </div>

    <?php
    if ($result->num_rows > 0) {
        // Output table header
        echo "<table>";
        echo "<tr><th>#</th><th>Date - Time</th><th>Home</th><th>Away</th><th>Venue</th><th>Toss And Decision</th><th>Result</th><th>Actions</th></tr>";

        // Calculate the starting match number for the current page
        $start_match_number = $total_records_fetched - $offset;

        // Start row number for the current page
        $row_number = $start_match_number;

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row_number . "</td>"; // Display the match number
            $formatted_date = date("D, j M Y", strtotime($row['date']));
            $match_time = ($row['is_evening_match'] == 0) ? "3:30 PM" : "7:30 PM";
            echo "<td>" . $formatted_date . " - " . $match_time . "</td>";
            echo "<td>" . $row['home_team'] . "</td>";
            echo "<td>" . $row['away_team'] . "</td>";
            echo "<td>" . $row['venue'] . "</td>";
            if (empty($row['toss']) || empty($row['decision'])) {
                echo "<td>No Toss</td>";
            } else {
                echo "<td>" . htmlspecialchars($row['toss']) . " decided to " . htmlspecialchars($row['decision']) . "</td>";
            }
            echo "<td>" . $row['result'] . "</td>";
            echo "<td class='action-buttons'>";
            // Enable update button regardless of result value
            echo "<button class='update-btn' data-match-no='" . $row['match_no'] . "' data-result='" . $row['result'] . "'><i class='fa-solid fa-pen-to-square'></i></button>";
            echo "<button class='delete-btn' onclick='deleteMatch(" . $row['match_no'] . ")'><i class='fa-solid fa-trash'></i></button>";
            echo "</td>";
            echo "</tr>";
            $row_number--;
        }
        echo "</table>";

        // If a team filter is applied, append it to the query string to preserve filtering across pages
        $query_string = !empty($team_filter) ? "&team=" . urlencode($team_filter) : "";

        // Pagination controls
        echo "<div class='pagination'>";
        // Show "Previous" link only if not on the first page
        if ($page > 1) {
            echo "<a href='?page=" . ($page - 1) . $query_string . "' class='prev'>Previous</a>";
        }

        // Generate page number links and highlight the current page as active
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='?page=" . $i . $query_string . "' class='" . ($i == $page ? 'active' : '') . "'>" . $i . "</a>";
        }

        // Show "Next" link only if not on the last page
        if ($page < $total_pages) {
            echo "<a href='?page=" . ($page + 1) . $query_string . "' class='next'>Next</a>";
        }
        echo "</div>";
    } else {
        echo '<div style="text-align: center; font-size: 25px;">No data found.</div>';
    }
    ?>

    <p><a href="create_match.php">Add New Match</a> | <a href="admin_dashboard.php">Go To Dashboard</a> | <a
            href="logout.php">Logout</a></p>

    <script>
        function updateMatch(matchNo, result) {
            // Check if result is not empty
            if (result !== "") {
                // Temporarily disabled: redirect to edit_match.php
                alert("Match editing is temporarily disabled. Please delete and re-add the match to make changes.");

                // window.location.href = "edit_match.php?match_no=" + matchNo;
            } else {
                // Redirect to update_match.php if result is empty
                window.location.href = "update_match.php?match_no=" + matchNo;
            }
        }

        function deleteMatch(matchNo) {
            // Display a confirmation dialog
            if (confirm('Are you sure you want to delete this match?')) {
                // If user confirms, redirect to delete page with the match number
                window.location.href = "delete_match.php?match_no=" + matchNo;
            }
        }

        // Add event listener to update buttons for extra caution, in case gets clicked through some external script or browser quirk
        document.querySelectorAll('.update-btn').forEach(button => {
            button.addEventListener('click', function (event) {
                updateMatch(button.getAttribute('data-match-no'), button.getAttribute('data-result'));
            });
        });
    </script>

    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>
</body>

</html>