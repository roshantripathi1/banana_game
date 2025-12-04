<?php
// get_leaderboard.php
// Returns top 5 scores (username, score, time_taken) for a difficulty.

require_once "config.php";

header("Content-Type: application/json");

try {
    $difficulty = $_GET['difficulty'] ?? 'beginner';

    $allowed = ['beginner', 'intermediate', 'advanced'];
    if (!in_array($difficulty, $allowed, true)) {
        $difficulty = 'beginner';
    }

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
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} catch (Throwable $e) {
    echo json_encode([
        'error'   => 'Failed to load leaderboard',
        'details' => $e->getMessage()
    ]);
}
