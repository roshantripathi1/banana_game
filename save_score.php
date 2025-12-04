<?php
// save_score.php
// Receives JSON from JS and saves a score in DB.

require_once "config.php";

header("Content-Type: application/json");

// Only logged users can save
$userId = AuthService::getUserId();
if ($userId === null) {
    echo json_encode(['status' => 'error', 'message' => 'Guests cannot save scores.']);
    exit;
}

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

$difficulty = $data["difficulty"] ?? "";
$score      = (int)($data["score"] ?? 0);
$timeTaken  = (int)($data["time_taken"] ?? 0);

// Basic validation
if ($difficulty === "" || $score <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload.']);
    exit;
}

// Save via service
ScoreService::saveScore($userId, $difficulty, $score, $timeTaken);

echo json_encode(['status' => 'ok']);
