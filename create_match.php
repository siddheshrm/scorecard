<?php
include 'session_handler.php';
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Match</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="css/create_match.css">
</head>

<body>
    <h2>Add New Match</h2>
    <form id="createMatchForm" method="POST" action="match_scorecard.php">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required max="<?php echo date('Y-m-d'); ?>"><br>

        <label for="team1">Home Team:</label>
        <select id="team1" name="team1" onchange="updateVenue(); updateTossWonBy();" required>
            <option value="">Select Team</option>
            <!-- Populate team options dynamically -->
            <?php
            $sql = "SELECT short_name, team_name FROM teams";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['short_name'] . "'>" . $row['team_name'] . "</option>";
                }
            }
            ?>
        </select><br>

        <label for="team2">Visiting Team:</label>
        <select id="team2" name="team2" onchange="updateVenue(); updateTossWonBy();" required>
            <option value="">Select Team</option>
            <!-- Populate team options dynamically -->
            <?php
            // Use the same query result to populate team options
            if ($result->num_rows > 0) {
                $result->data_seek(0); // Reset pointer to start of result set
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['short_name'] . "'>" . $row['team_name'] . "</option>";
                }
            }
            ?>
        </select><br>

        <label for="venue">Venue:</label>
        <input type="text" id="venue" name="venue" readonly>
        <br>

        <label for="toss_won_by">Toss Won By:</label>
        <select id="toss_won_by" name="toss_won_by" required>
            <option value="">Select Team</option>
            <!-- Options will be dynamically filled based on home and away team selection -->
        </select><br>

        <label for="decided_to">Decided To:</label>
        <select id="decided_to" name="decided_to" required>
            <option value="">Select</option>
            <option value="bat">Bat</option>
            <option value="bowl">Bowl</option>
        </select><br>

        <input type="submit" value="Create Match">
    </form>

    <p><a href="view_matches.php">See All Matches</a> | <a href="admin_dashboard.php">Go To Dashboard</a></p>
    <p><a href="index.php">Logout</a></p>

    <script>
        function updateVenue() {
            var team1 = document.getElementById("team1").value;
            var team2 = document.getElementById("team2").value;

            if (team1 === team2 && team1 !== "" && team2 !== "") {
                alert("Please select different teams.");
                document.getElementById("team2").value = "";
                return;
            }

            var venueOptions = {
                "MI": "Wankhede Stadium",
                "CSK": "MA Chidambaram Stadium",
                "RCB": "M Chinnaswamy Stadium",
                "RR": "Sawai Mansingh Stadium",
                "SRH": "Rajiv Gandhi International Cricket Stadium",
                "KKR": "Eden Gardens",
                "DC": "Arun Jaitley Stadium",
                "PKS": "Punjab Cricket Association Stadium",
                "GG": "Narendra Modi Stadium",
                "LSG": "Bharat Ratna Shri Atal Bihari Vajpayee Ekana Cricket Stadium"
            };

            var venueDropdown = document.getElementById("venue");
            venueDropdown.innerHTML = "";
            venueDropdown.innerHTML += "<option value=''>Select Venue</option>";
            if (venueOptions[team1] && venueOptions[team2]) {
                venueDropdown.innerHTML += "<option value='" + venueOptions[team1] + "'>" + venueOptions[team1] + "</option>";
                venueDropdown.value = venueOptions[team1]; // Auto-select venue based on home team
            }
        }

        function updateTossWonBy() {
            var team1 = document.getElementById("team1").value;
            var team2 = document.getElementById("team2").value;

            var tossWonByDropdown = document.getElementById("toss_won_by");
            tossWonByDropdown.innerHTML = ""; // Clear existing options

            // Create and append the default option
            var defaultOption = document.createElement("option");
            defaultOption.text = "Select Team";
            tossWonByDropdown.add(defaultOption);

            // Add options for team1 and team2 if they are selected
            if (team1 && team2) {
                var team1Option = document.createElement("option");
                team1Option.value = team1;
                team1Option.text = team1;
                tossWonByDropdown.add(team1Option);

                var team2Option = document.createElement("option");
                team2Option.value = team2;
                team2Option.text = team2;
                tossWonByDropdown.add(team2Option);
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            updateVenue();
            updateTossWonBy();
        });

        document.getElementById("createMatchForm").addEventListener("submit", function (event) {
            if (!confirm("Are you sure you want to create this match?")) {
                event.preventDefault(); // Prevent form submission if user cancels
            }
        });
    </script>

</body>

</html>