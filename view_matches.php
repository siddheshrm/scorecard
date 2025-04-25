<?php
include 'session_handler.php';
include 'config.php';

// Pagination setup
$limit = 8;
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$offset = ($page - 1) * $limit; // Offset calculation

// Query to get paginated records
$sql = "SELECT * FROM tournament_data ORDER BY date DESC, is_evening_match DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Total records count for pagination
$total_sql = "SELECT COUNT(*) AS total FROM tournament_data";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_records_fetched = $total_row['total'];

$total_pages = ceil($total_records_fetched / $limit); // Total pages required
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament History</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="css/view_matches.css">
</head>

<body>
    <h2>Tournament History</h2>

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
            echo "<td>" . $row['toss'] . " decided to " . $row['decision'] . " first</td>";
            echo "<td>" . $row['result'] . "</td>";
            echo "<td class='action-buttons'>";
            // Enable update button regardless of result value
            echo "<button class='update-btn' data-match-no='" . $row['match_no'] . "' data-result='" . $row['result'] . "'>Update</button>";
            echo "<button class='delete-btn' onclick='deleteMatch(" . $row['match_no'] . ")'>Delete</button>";
            echo "</td>";
            echo "</tr>";
            $row_number--;
        }
        echo "</table>";

        // Pagination controls
        echo "<div class='pagination'>";
        if ($page > 1) {
            echo "<a href='?page=" . ($page - 1) . "' class='prev'>Previous</a>";
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='?page=" . $i . "' class='" . ($i == $page ? 'active' : '') . "'>" . $i . "</a>";
        }

        if ($page < $total_pages) {
            echo "<a href='?page=" . ($page + 1) . "' class='next'>Next</a>";
        }
        echo "</div>";
    } else {
        echo '<div style="text-align: center; font-size: 25px;">No data found.</div>';
    }
    ?>

    <p><a href="create_match.php">Add New Match</a> | <a href="admin_dashboard.php">Go To Dashboard</a> | <a href="logout.php">Logout</a></p>

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