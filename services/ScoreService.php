<?php
// services/ScoreService.php
// All DB logic for scores + leaderboard.

require_once __DIR__ . "/Database.php";

class ScoreService
{
    // Save one score row
    public static function saveScore(int $userId, string $difficulty, int $score, int $timeTaken): void
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO scores (user_id, difficulty, score, time_taken)
            VALUES (:user_id, :difficulty, :score, :time_taken)
        ");

        $stmt->execute([
            ':user_id'    => $userId,
            ':difficulty' => $difficulty,
            ':score'      => $score,
            ':time_taken' => $timeTaken
        ]);
    }

    // Get top N scores for a difficulty
    public static function getTopScores(string $difficulty, int $limit = 5): array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT u.username, s.score, s.time_taken
            FROM scores s
            JOIN users u ON s.user_id = u.id
            WHERE s.difficulty = :difficulty
            ORDER BY s.score DESC, s.time_taken ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':difficulty', $difficulty, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
