<?php
include '../config.php';

// Path to the CSV file
$csvFile = 'DLS.csv';

// Open the file for reading
if (($handle = fopen($csvFile, 'r')) !== FALSE) {
    // Get the first row, which contains the column names
    $columns = fgetcsv($handle, 1000, ',');

    // Loop through the file line-by-line
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        // Prepare the SQL insert statement
        $sql = "INSERT INTO dls_calculation (overs_left, wickets_lost_0, wickets_lost_1, wickets_lost_2, wickets_lost_3, wickets_lost_4, wickets_lost_5, wickets_lost_6, wickets_lost_7, wickets_lost_8, wickets_lost_9, wickets_lost_10) 
                VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]', '$data[5]', '$data[6]', '$data[7]', '$data[8]', '$data[9]', '$data[10]', '$data[11]')";

        // Execute the query
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully\n";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error . "\n";
        }
    }
    // Close the file
    fclose($handle);
} else {
    echo "Error opening the file.";
}

// Close the database connection
$conn->close();
