<?php
// save_score.php
// Saves score for logged-in user into scores table.

require_once "config.php";

header("Content-Type: application/json");
session_start();

try {
    // Check login
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }

    $pdo = Database::getConnection(); // from config.php

    // Resolve user_id
    if (isset($_SESSION['user_id'])) {
        $userId = (int) $_SESSION['user_id'];
    } else {
        $username = $_SESSION['username'];
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :uname");
        $stmt->execute([':uname' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            echo json_encode(['error' => 'User not found']);
            exit;
        }
        $userId = (int) $row['id'];
        $_SESSION['user_id'] = $userId; // cache
    }

    // Read POST
    $score      = $_POST['score']      ?? null;
    $time       = $_POST['time']       ?? null;
    $difficulty = $_POST['difficulty'] ?? null;

    if ($score === null || $time === null || $difficulty === null) {
        echo json_encode(['error' => 'Missing data']);
        exit;
    }

    // Insert row
    $stmt = $pdo->prepare("
        INSERT INTO scores (user_id, score, time_taken, difficulty)
        VALUES (:uid, :score, :time_taken, :difficulty)
    ");
    $stmt->execute([
        ':uid'        => $userId,
        ':score'      => (int) $score,
        ':time_taken' => (int) $time,
        ':difficulty' => $difficulty
    ]);

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    echo json_encode([
        'error'   => 'Failed to save score',
        'details' => $e->getMessage()
    ]);
}
