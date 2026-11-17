<?php
// Labdoo Hub Lookup
// Usage: hub-lookup.php?city=Stade
// Returns the matching open hub line(s) from hubs_clean.txt

$city = isset($_GET['city']) ? trim($_GET['city']) : '';

if (empty($city)) {
    http_response_code(400);
    echo "Missing city parameter. Usage: hub-lookup.php?city=CityName";
    exit;
}

$file = __DIR__ . '/wp-content/uploads/hubs_clean.txt';

if (!file_exists($file)) {
    http_response_code(500);
    echo "Hub list not found.";
    exit;
}

$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$results = [];

foreach ($lines as $line) {
    // Each line format: City, Country → URL
    if (stripos($line, $city) !== false) {
        $results[] = $line;
    }
}

header('Content-Type: text/plain; charset=utf-8');

if (empty($results)) {
    echo "No open hub found for: " . htmlspecialchars($city) . "\n";
    echo "Please check the full list at: https://platform.labdoo.org/content/hubs-dashboard";
} else {
    echo implode("\n", $results);
}
