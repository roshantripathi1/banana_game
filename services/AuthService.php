<?php
// services/AuthService.php
// Handles session + login state.

require_once __DIR__ . "/Database.php";

class AuthService
{
    // Ensure session started
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Force login; if not logged, go to login page
    public static function requireLogin(): void
    {
        self::initSession();
        if (empty($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
    }

    // True if no logged-in user
    public static function isGuest(): bool
    {
        self::initSession();
        return empty($_SESSION['user_id']);
    }

    // Get current user id (or null)
    public static function getUserId(): ?int
    {
        self::initSession();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    // Get current username (or "Guest")
    public static function getUsername(): string
    {
        self::initSession();
        return $_SESSION['username'] ?? "Guest";
    }

    // Log out and destroy session
    public static function logout(): void
    {
        self::initSession();
        session_unset();
        session_destroy();
    }
}
