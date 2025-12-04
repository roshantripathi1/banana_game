<?php
// config.php
// Load all services and start session once.

require_once __DIR__ . "/services/Database.php";
require_once __DIR__ . "/services/AuthService.php";
require_once __DIR__ . "/services/UserService.php";
require_once __DIR__ . "/services/ScoreService.php";
require_once __DIR__ . "/services/BananaService.php";

// Start session
AuthService::initSession();

// Optional: global PDO if you need it anywhere
$pdo = Database::getConnection();
?>