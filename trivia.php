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
