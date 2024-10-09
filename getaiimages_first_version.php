<?php

$url = 'https://deepdreamgenerator.com/search-text';
$params = [
    'q' => 'laptop',
    'offset' => 0,
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

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
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

    foreach ($urlsArray as &$url) {
        $url = stripslashes($url);
        if (substr($url, -1) === "n") {
            $url = substr($url, 0, -1);
        }
    }
    unset($url);

    echo "Clickable Links:<br>";
    $imagesArray = [];
    foreach ($urlsArray as $url) {
        $imagesArray[]  = trim($url, '"');
        echo "<a href=\"" . htmlspecialchars(trim($url, '"')) . "\" target=\"_blank\">" . htmlspecialchars(trim($url, '"')) . "</a><br>";
    }
    $jsonOutput = json_encode($imagesArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "<pre>JSON Output: " . $jsonOutput . "</pre><br><br>";
}

curl_close($ch);
?>