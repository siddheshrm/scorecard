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
    <link href="https://fonts.googleapis.com/css2?family=Tuffy:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/logos/IPL.png" type="image/png">
    <link rel="stylesheet" href="css/create_match.css">
</head>

<body>
    <h2>Add New Match</h2>
    <form id="createMatchForm" method="POST" action="match_scorecard.php">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required max="<?php echo date('Y-m-d'); ?>"><br>

        <label for="is_evening_match">Match Time:</label>
        <select name="is_evening_match" required>
            <option value="1" selected>7:30 PM</option>
            <option value="0">3:30 PM</option>
        </select>

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
        <select id="venue" name="venue" required>
            <option value="">Select Venue</option>
        </select><br>

        <label for="toss_status">Toss Status:</label>
        <select id="toss_status" name="toss_status" onchange="handleMatchStatusChange();" required>
            <option value="abandoned_after_toss" selected>Toss Completed</option>
            <option value="abandoned_before_toss">Match Abandoned Without A Toss</option>
        </select><br>

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
    <p><a href="logout.php">Logout</a></p>

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
                "MI": ["Wankhede Stadium, Mumbai"],
                "CSK": ["MA Chidambaram Stadium, Chennai"],
                "RCB": ["M. Chinnaswamy Stadium, Bengaluru"],
                "RR": ["Sawai Mansingh Stadium, Jaipur", "Barsapara Cricket Stadium, Guwahati"],
                "SRH": ["Rajiv Gandhi International Cricket Stadium, Hyderabad"],
                "KKR": ["Eden Gardens, Kolkata"],
                "DC": ["Arun Jaitley Stadium, Delhi", "Dr. Y.S. Rajasekhara Reddy ACA-VDCA Cricket Stadium, Visakhapatnam"],
                "PBKS": ["Maharaja Yadavindra Singh International Cricket Stadium, Chandigarh", "Himachal Pradesh Cricket Association Stadium, Dharamsala"],
                "GT": ["Narendra Modi Stadium, Ahmedabad"],
                "LSG": ["Bharat Ratna Shri Atal Bihari Vajpayee Ekana Cricket Stadium, Lucknow"]
            };

            var venueDropdown = document.getElementById("venue");
            venueDropdown.innerHTML = "<option value=''>Select Venue</option>"; // Reset dropdown

            // TEMPORARY CHANGE FOR IPL 2025 NEUTRAL VENUES
            // Show all venues instead of filtering by home team
            // Create a Set to keep track of venues already added (to avoid duplicates)
            let addedVenues = new Set();

            // Loop through each team in the venueOptions object
            for (let team in venueOptions) {
                // Loop through each venue of the current team
                venueOptions[team].forEach(function (venue) {
                    // Check if this venue has not been added yet
                    if (!addedVenues.has(venue)) {
                        // Add the venue to the Set to mark it as added
                        addedVenues.add(venue);

                        // Create a new <option> element
                        var option = document.createElement("option");
                        // Set the value of the <option> to the venue name
                        option.value = venue;
                        // Set the visible text of the <option> to the venue name
                        option.textContent = venue;
                        // Add the <option> to the venue dropdown in the DOM
                        venueDropdown.appendChild(option);
                    }
                });
            }

            // OLD LOGIC (commented for future reactivation)
            /*if (venueOptions[team1]) {
                venueOptions[team1].forEach(function (venue) {
                    var option = document.createElement("option");
                    option.value = venue;
                    option.textContent = venue;
                    venueDropdown.appendChild(option);
                });

                if (venueOptions[team1].length === 1) {
                    venueDropdown.value = venueOptions[team1][0]; // Auto-select if only one venue
                }
            }*/
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

    <script>
        function handleMatchStatusChange() {
            const status = document.getElementById("toss_status").value;
            const toss = document.getElementById("toss_won_by");
            const decision = document.getElementById("decided_to");

            if (status === "abandoned_before_toss") {
                toss.disabled = true;
                decision.disabled = true;
                toss.required = false;
                decision.required = false;
            } else {
                toss.disabled = false;
                decision.disabled = false;
                toss.required = true;
                decision.required = true;
            }
        }
    </script>

    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>
</body>

</html>