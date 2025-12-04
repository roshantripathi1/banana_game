<?php
require_once "config.php";

header("Content-Type: application/json");

$difficulty = $_GET['difficulty'] ?? 'beginner';

// Only return username, score, time — NO DATE
$pdo = Database::getConnection();
$stmt = $pdo->prepare("
    SELECT u.username, s.score, s.time_taken
    FROM scores s
    JOIN users u ON s.user_id = u.id
    WHERE s.difficulty = :diff
    ORDER BY s.score DESC, s.time_taken ASC
    LIMIT 5
");

$stmt->execute([':diff' => $difficulty]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
