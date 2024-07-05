<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Data Table</title>
    <link rel="stylesheet" href="css/view_matches.css">
</head>

<body>
    <h2>Tournament History</h2>

    <?php
    // Include the configuration file
    include 'config.php';

    // Query to retrieve all data from tournament_data table
    $sql = "SELECT * FROM tournament_data ORDER BY match_no DESC"; // Order by match_no in descending order

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output table header
        echo "<table>";
        echo "<tr><th>Match No.</th><th>Date</th><th>Home</th><th>Away</th><th>Venue</th><th>Toss</th><th>Decision</th><th>Result</th><th>Actions</th></tr>";

        // Output data of each row
        $row_number = $result->num_rows; // Set initial row number to the number of rows in the result set
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row_number . "</td>"; // Display the row number
            $formatted_date = date("F j, Y", strtotime($row['date']));
            echo "<td>" . $formatted_date . "</td>";
            echo "<td>" . $row['home_team'] . "</td>";
            echo "<td>" . $row['away_team'] . "</td>";
            echo "<td>" . $row['venue'] . "</td>";
            echo "<td>" . $row['toss'] . "</td>";
            echo "<td>" . $row['decision'] . "</td>";
            echo "<td>" . $row['result'] . "</td>";
            echo "<td class='action-buttons'>";
            // Enable update button only if result is empty
            if (empty($row['result'])) {
                echo "<button class='update-btn' onclick='updateMatch(" . $row['match_no'] . ")'>Update</button>";
            } else {
                echo "<button class='update-btn' disabled>Update</button>";
            }
            // Enable delete button if result is not empty
            echo "<button class='delete-btn' onclick='deleteMatch(" . $row['match_no'] . ")'>Delete</button>";
            echo "</td>";
            echo "</tr>";
            $row_number--;
        }
        echo "</table>";
    } else {
        echo '<div style="text-align: center; font-size: 25px;">No data found.</div>';
    }
    ?>
    <p><a href="create_match.php">Insert Match Data</a></p>
    <p><a href="admin_dashboard.php">Dashboard</a></p>
    <p><a href="index.php">Logout</a></p>

    <script>
        function updateMatch(matchNo) {
            // Redirect to update page with the match number
            window.location.href = "update_match.php?match_no=" + matchNo;
        }

        function deleteMatch(matchNo) {
            // Display a confirmation dialog
            if (confirm('Are you sure you want to delete this match?')) {
                // If user confirms, redirect to delete page with the match number
                window.location.href = "delete_match.php?match_no=" + matchNo;
            }
        }
    </script>

</body>

</html>