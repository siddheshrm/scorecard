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
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/table.css">
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
</body>

</html>