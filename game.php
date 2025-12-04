<?php
// game.php
// Main game page (HTML + links to JS/CSS).

require_once "config.php";

// Get login / guest info
$isGuest  = AuthService::isGuest();
$username = AuthService::getUsername();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Banana Puzzle Game</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
<header class="top-bar">
    <!-- Show current user name -->
    <div>Welcome, <?= htmlspecialchars($username) ?></div>

    <!-- Right side controls: difficulty + nav buttons -->
    <div class="top-right-controls">
        Difficulty:
        <select id="difficulty">
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
        </select>

        <span class="level-label">Level: <span id="level">1</span></span>

        <button id="start-game">Start Game</button>

        <?php if (!$isGuest): ?>
            <button onclick="window.location='profile.php'">Profile</button>
            <button onclick="window.location='logout.php'">Logout</button>
        <?php else: ?>
            <button onclick="window.location='login.php'">Login</button>
            <button onclick="window.location='register.php'">Register</button>
        <?php endif; ?>
    </div>
</header>

<main class="game-layout">
    <!-- LEFT: statistics -->
    <section class="game-info">
        <h2>Stats</h2>
        <p>Score: <span id="score">0</span></p>
        <p>Time Left: <span id="timer">0</span> s</p>
        <p>Hints Left: <span id="hints">0</span></p>
        <button id="use-hint">Use Hint</button>
        <p class="hint-note">Hints briefly reveal one matching pair.</p>
    </section>

    <!-- CENTER: memory board -->
    <section class="board-container">
        <h2>Puzzle Board</h2>
        <div id="game-board" class="board"></div>
    </section>

    <!-- RIGHT: math challenge + leaderboard -->
    <section class="sidebar">
       <h2>Math Challenge</h2>
<div id="math-challenge">
    <!-- Banana puzzle image from API -->
    <img id="banana-image" alt="Banana puzzle"
         style="max-width:100%; display:none; margin-bottom:10px;">

               <p id="math-question">Start a game to unlock the math challenge.</p>
                <input type="number" id="math-answer" placeholder="Your answer" disabled>
                        <button id="submit-math" disabled>Submit Answer</button>
    <p id="math-feedback"></p>
</div>


        <h2>Leaderboard (Top 5)</h2>
        <p class="leaderboard-note">Scores are saved only when you are logged in.</p>
        <ul id="leaderboard"></ul>
    </section>
</main>

<!-- Pass PHP values to JS -->
<script>
    const CURRENT_USER = "<?= htmlspecialchars($username) ?>";
    const IS_GUEST     = <?= $isGuest ? 'true' : 'false' ?>;
</script>

<!-- Game logic -->
<script src="public/script.js"></script>
</body>
</html>
