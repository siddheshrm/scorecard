<?php
include '../config.php';

// Path to the CSV file
$csvFile = 'DLS.csv';

// Initialize counters
$successCount = 0;
$errorCount = 0;

// Open the file for reading
if (($handle = fopen($csvFile, 'r')) !== FALSE) {
    // Get the first row, which contains the column names
    $columns = fgetcsv($handle, 1000, ',');

    // Loop through the file line-by-line
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        // Check if the overs_left already exists in the database
        $overs_left = $data[0];
        $checkQuery = "SELECT COUNT(*) FROM dls_calculation WHERE overs_left = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $overs_left);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            // If the record exists, skip this row
            $errorCount++;
            // Skip inserting this row, but continue with the next one
            continue;
        }

        // Insert the new record if it doesn't already exist
        $sql = "INSERT INTO dls_calculation (overs_left, wickets_lost_0, wickets_lost_1, wickets_lost_2, wickets_lost_3, wickets_lost_4, wickets_lost_5, wickets_lost_6, wickets_lost_7, wickets_lost_8, wickets_lost_9, wickets_lost_10) 
                VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]', '$data[5]', '$data[6]', '$data[7]', '$data[8]', '$data[9]', '$data[10]', '$data[11]')";

        // Execute the query
        if ($conn->query($sql) === TRUE) {
            $successCount++;
        } else {
            // Capture database errors
            $errorCount++;
        }
    }
    fclose($handle);

    // Display a summary message after all records are processed
    if ($successCount > 0) {
        echo "<script>
                alert('$successCount records successfully inserted.');
              </script>";
    }

    // Show a simple error message if there were any errors
    if ($errorCount > 0) {
        echo "<script>
                alert('$errorCount records failed to import due to errors (e.g., duplicates or database issues).');
                window.location.href = '../index.php';
              </script>";
    } else {
        echo "<script>
                window.location.href = '../index.php';
              </script>";
    }
} else {
    // If the file can't be opened, show an alert
    echo "<script>
            alert('Error opening the file.');
            window.location.href = '../index.php';
          </script>";
}
$conn->close();
?>