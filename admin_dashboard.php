<?php
include 'session_handler.php';
include 'config.php';

$username = $_SESSION['username'];

// Query the database to fetch user details based on the username
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User found, fetch user details
    $row = $result->fetch_assoc();
    $name = $row['name'];
} else {
    echo "Error: User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="icon" href="./media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="css/table.css">
</head>

<body>
    <h2>Welcome to dashboard, <?php echo $name; ?>.</h2>
    <?php include 'points_table.php'; ?>
    <p><a href="register.php">Register New Admin</p>
    <p><a href="create_match.php">Add New Match</a> | <a href="view_matches.php">See All Matches</a>
    </p>
    <p><a href="logout.php">Logout</a></p>
</body>

</html>