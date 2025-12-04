<?php
// banana_api.php
// Called by script.js. Uses BananaService to reach the real API.
// Default: out=json&base64=no; can switch base64 via query.

require_once "config.php";

header("Content-Type: application/json");

// Allow ?base64=yes from JS if you ever want base64 image
$useBase64 = (isset($_GET['base64']) && $_GET['base64'] === 'yes');

// Get JSON puzzle from service
$result = BananaService::getPuzzleJson($useBase64);

// Keep answer for math, plus image info if needed
echo json_encode([
    'answer'       => $result['answer'],        // numeric solution
    'image_url'    => $result['image_url'],     // image link when base64=no
    'image_base64' => $result['image_base64'],  // base64 when base64=yes
    'raw'          => $result['raw'],           // full JSON (optional)
    'error'        => $result['error']
]);
