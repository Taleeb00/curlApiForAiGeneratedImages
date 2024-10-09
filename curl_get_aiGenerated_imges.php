<?php

// Retrieve parameters from URL and validate them
$q = isset($_GET['q']) && !empty($_GET['q']) ? $_GET['q'] : null;
$offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? $_GET['offset'] : 0;

if (is_null($q)) {
    die("Error: Missing or empty 'q' parameter.");
}

$url = 'https://deepdreamgenerator.com/search-text';
$params = [
    'q' => $q,
    'offset' => $offset,
    'dataType' => 'json'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} elseif ($httpCode !== 200) {
    echo "Error: Received HTTP code $httpCode. Please try again.";
} else {
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($response);
    libxml_clear_errors();

    $images = $dom->getElementsByTagName('img');
    $urlsArray = [];

    foreach ($images as $img) {
        if ($img->hasAttribute('data-src')) {
            $urlValue = $img->getAttribute('data-src');
            $urlsArray[] = trim($urlValue, '"');
        }
    }

    // Process and clean URLs
    foreach ($urlsArray as &$url) {
        $url = stripslashes($url);
        if (substr($url, -1) === "n") {
            $url = substr($url, 0, -1);
        }
    }
    unset($url);

    // Output JSON result
    if (!empty($urlsArray)) {
        echo "Clickable Links:<br>";
        $imagesArray = [];
        foreach ($urlsArray as $url) {
            $imagesArray[]  = trim($url, '"');
        }
        $jsonOutput = json_encode($imagesArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo "<pre>JSON Output: " . $jsonOutput . "</pre><br><br>";
    } else {
        echo "No images found.";
    }
}

curl_close($ch);
?>
