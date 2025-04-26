<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to scorecard.com</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tuffy:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/logos/IPL.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/table.css">
    <link rel="stylesheet" href="css/match_details.css">
</head>

<body>
    <!-- <h2>Welcome to <i>scorecard.com</i></h2> -->
    <?php include 'points_table.php'; ?>
    <p>
        <a href="admin_login.php">Admin Login</a>
    </p>

    <p><a href="strike_rate/strike_rate.php">Strike Rate Calculator</a> | <a
            href="dls/dls_calculator.php">Duckworth-Lewis-Stern Par Score Calculator</a></p><br>

    <!-- Include Trivia -->
    <?php include './trivia/trivia.php'; ?>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<script>alert('" . htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['message']);
    }
    ?>

    <!-- Modal is inserted dynamically here -->
    <div id="modal-container"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".dropdown-icon").forEach(icon => {
                icon.addEventListener("click", function () {
                    let team = this.getAttribute("data-team");

                    // Fetch modal content from match_details.php
                    fetch("match_details.php?team=" + team)
                        .then(response => response.text())
                        .then(data => {
                            let modalContainer = document.getElementById("modal-container");
                            modalContainer.innerHTML = data;

                            // Find the modal element inside newly inserted content
                            let modal = document.getElementById("matchModal");
                            if (!modal) {
                                console.error("Modal element not found.");
                                return;
                            }

                            modal.style.display = "flex"; // Show modal

                            // Attach close event after modal is added
                            let closeBtn = modal.querySelector(".close");
                            if (closeBtn) {
                                closeBtn.addEventListener("click", closeModal);
                            }
                        })
                        .catch(error => console.error("Error loading modal:", error));
                });
            });
        });

        // Function to close the modal
        function closeModal() {
            const modal = document.getElementById("matchModal");
            if (modal) {
                modal.remove();
            }
        }
    </script>

    <script src="https://kit.fontawesome.com/9dd0cb4077.js" crossorigin="anonymous"></script>
</body>

</html>