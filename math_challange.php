<?php
// ==============================
// MATH_CHALLENGE.PHP
// - Frontend calls this file via fetch()
// - It tries to get a math question from Banana API
// - If Banana fails or is not set up, it falls back to local random math
// ==============================

header('Content-Type: application/json');

// ----------------------------------------
// 1) CONFIGURE YOUR BANANA API DETAILS HERE
//    (Use environment variables in real project if possible.)
// ----------------------------------------

// Option A: Read from environment variables (recommended)
$BANANA_API_KEY   = getenv('BANANA_API_KEY');
$BANANA_MODEL_KEY = getenv('BANANA_MODEL_KEY');

// Option B: Hard-code for demo (NOT recommended for real deployments)
// $BANANA_API_KEY   = "YOUR_BANANA_API_KEY_HERE";
// $BANANA_MODEL_KEY = "YOUR_BANANA_MODEL_KEY_HERE";

// ----------------------------------------
// 2) Try to get question from Banana API
// ----------------------------------------
if (!empty($BANANA_API_KEY) && !empty($BANANA_MODEL_KEY)) {
    $bananaResult = getMathFromBanana($BANANA_API_KEY, $BANANA_MODEL_KEY);

    // If Banana returned a valid question + answer, send it to JS and exit
    if ($bananaResult !== null && isset($bananaResult['question'], $bananaResult['answer'])) {
        echo json_encode([
            'source'   => 'banana_api',
            'question' => $bananaResult['question'],
            'answer'   => $bananaResult['answer']
        ]);
        exit;
    }
}

// ----------------------------------------
// 3) FALLBACK: Local random math generator
//    (Used if Banana is not configured or call failed)
// ----------------------------------------
$a  = rand(1, 20);
$b  = rand(1, 20);
$op = rand(0, 1) ? '+' : '-';

$question = "$a $op $b";
$answer   = ($op === '+') ? $a + $b : $a - $b;

echo json_encode([
    'source'   => 'local_fallback',
    'question' => $question,
    'answer'   => $answer
]);
exit;

// ==============================
// HELPER FUNCTION: Call Banana API
// ==============================

/**
 * Calls Banana API to generate a math question.
 * EXPECTED Banana app behaviour (your custom model):
 *  - Request input might contain: { "type": "math_question" }
 *  - Response JSON (example):
 *      {
 *        "question": "5 + 3",
 *        "answer": 8
 *      }
 *
 * @param string $apiKey
 * @param string $modelKey
 * @return array|null  ['question' => string, 'answer' => int] or null on failure
 */
function getMathFromBanana(string $apiKey, string $modelKey): ?array
{
    // ---------- Banana API endpoint (v4 style example) ----------
    // IMPORTANT: Check latest Banana docs for the newest endpoint.
    // This is a generic example for assignments.
    $url = "http://marcconrad.com/uob/banana/api.php";

    // Input we send to our Banana app
    $payload = [
        "apiKey"   => $apiKey,
        "modelKey" => $modelKey,
        "input"    => [
            "type" => "math_question"
        ]
    ];

    // Initialize cURL
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 10
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        // You can log error if needed
        curl_close($ch);
        return null;
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode !== 200 || !$response) {
        return null;
    }

    // Decode JSON from Banana
    $json = json_decode($response, true);
    if (!is_array($json)) {
        return null;
    }

    // ------------------------------
    // IMPORTANT:
    // The exact structure of $json depends on your Banana app.
    // For assignment, we assume the Banana app returns:
    // { "question": "7 + 5", "answer": 12 }
    // Adjust mapping below to match your real response.
    // ------------------------------

    if (isset($json['question'], $json['answer'])) {
        return [
            'question' => (string)$json['question'],
            'answer'   => (int)$json['answer']
        ];
    }

    // If your real Banana response nests data, e.g.
    // { "modelOutputs": [{ "question": "...", "answer": ... }] }
    // you would adjust like this:
    //
    // if (isset($json['modelOutputs'][0]['question'], $json['modelOutputs'][0]['answer'])) {
    //     return [
    //         'question' => (string)$json['modelOutputs'][0]['question'],
    //         'answer'   => (int)$json['modelOutputs'][0]['answer']
    //     ];
    // }

    return null;
}
