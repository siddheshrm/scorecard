<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

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
    <link rel="icon" href="./media/scorecard.com.png" type="image/png">
    <link rel="stylesheet" href="css/table.css">
</head>

<body>
    <h2>Welcome to dashboard, <?php echo $name; ?>.</h2>
    <?php include 'points_table.php'; ?>
    <p><a href="register.php">Register New Admin</p>
    <p><a href="create_match.php">Insert New Match Data</a> | <a href="view_matches.php">Update Existing Match Data</a>
    </p>
    <p><a href="index.php">Logout</a></p>
</body>

</html>