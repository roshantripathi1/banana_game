<?php
// services/UserService.php
// All DB operations related to users.

require_once __DIR__ . "/Database.php";

class UserService
{
    // Get one user row (id, username, email)
    public static function getUserById(int $id): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Update email for user
    public static function updateEmail(int $id, string $email): bool
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        return $stmt->execute([$email, $id]);
    }

    // List all scores for this user
    public static function getUserScores(int $userId): array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT difficulty, score, time_taken, created_at
            FROM scores
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if given password matches stored hash
    public static function verifyPassword(int $id, string $plainPassword): bool
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return password_verify($plainPassword, $row['password_hash']);
    }

    // Update password for user
    public static function updatePassword(int $id, string $plainPassword): bool
    {
        $pdo = Database::getConnection();

        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");

        return $stmt->execute([$hash, $id]);
    }

    // Find user by username + email (for forgot password)
    public static function getUserByUsernameAndEmail(string $username, string $email): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = ? AND email = ?");
        $stmt->execute([$username, $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
