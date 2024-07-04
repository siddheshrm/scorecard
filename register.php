<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register here</title>
    <!-- <link rel="stylesheet" href="css/register.css"> -->

    <script>
        function fetchTrivia() {
            const today = new Date().getDate();
            fetch('https://cricket-trivia-api-2fa7961781d4.herokuapp.com/cricket_trivia')
                .then(response => response.json())
                .then(data => {
                    const trivia = data.find(item => item.id === today);
                    if (trivia) {
                        document.getElementById('trivia').innerHTML = `<h3>Cricket Trivia for Today</h3><p><strong>${trivia.title}:</strong> ${trivia.description}</p>`;
                    } else {
                        document.getElementById('trivia').innerHTML = `<p>No trivia found for today.</p>`;
                    }
                })
                .catch(error => console.error('Error fetching trivia:', error));
        }
    </script>

</head>

<body>
    <h2>Welcome to <i>scorecard.com</i></h2>
    <h3>Enter Details To Sign-up</h3>
    <form method="POST" action="register.php">
        Name* <input type="text" name="name" placeholder="minimum 5 characters" required><br><br>
        Age* <input type="number" name="age" required><br><br>
        Email <input type="email" name="email"><br><br>
        Favourite IPL Team <input type="text" name="favourite_ipl_team"><br><br>
        Mobile Number <input type="text" name="mobile_number"><br><br>
        Password* <input type="password" name="password" placeholder="minimum 5 characters" required><br><br>
        <input type="submit" value="Submit">
    </form>

    <p>Already have an account? <a href="index.php">Login here</a></p><br>

    <?php
    function fetchTrivia($id)
    {
        $url = "https://cricket-trivia-api-2fa7961781d4.herokuapp.com/cricket_trivia";
        $data = file_get_contents($url);
        $triviaArray = json_decode($data, true);

        foreach ($triviaArray as $trivia) {
            if ($trivia['id'] == $id) {
                return $trivia;
            }
        }
        return null;
    }

    $today = date("j"); // Day of the month without leading zeros
    $trivia = fetchTrivia($today);

    if ($trivia) {
        echo "<h3>Cricket Trivia for Today</h3>";
        echo "<p><strong>" . $trivia['title'] . ":</strong> " . $trivia['description'] . "</p>";
    } else {
        echo "<p>No trivia found for today.</p>";
    }
    ?>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include 'config.php';

        // Collect and validate input
        $name = $_POST['name'];
        $age = $_POST['age'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $favourite_ipl_team = $_POST['favourite_ipl_team'];
        $mobile_number = $_POST['mobile_number'];

        // Validate input
        if (strlen($name) < 5 || preg_match('/\d/', $name)) {
            echo "Name must be at least 5 characters long and contain no numbers.<br>";
        } elseif (empty($age)) {
            echo "Age is required.<br>";
        } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Invalid email address.<br>";
        } elseif (strlen($password) < 5) {
            echo "Password must be at least 5 characters long.<br>";
        } elseif (strlen($mobile_number) > 10) {
            echo "Mobile number cannot exceed 10 digits.<br>";
        } else {
            // Prepare SQL query
            $sql = "INSERT INTO users (name, age, email, password, favourite_ipl_team, mobile_number)
                VALUES ('$name', $age, ";

            // Include email field in SQL query if provided
            if (!empty($email)) {
                $sql .= "'$email', ";
            } else {
                $sql .= "NULL, "; // Use NULL if email is not provided
            }

            $sql .= "'$password', '$favourite_ipl_team', '$mobile_number')";

            // Execute SQL query
            $result = $conn->query($sql);
            if ($result === TRUE) {
                echo "New record created successfully";
                // Redirect to index.php
                header("Location: index.php");
                exit(); // Ensure that no other output is sent to the browser
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        $conn->close();
    }
    ?>
</body>

</html>