<?php
// profile.php
// Shows user info and score history, allows email change.

require_once "config.php";

// Only logged users allowed
AuthService::requireLogin();

$userId   = AuthService::getUserId();
$username = AuthService::getUsername();

// Load user data and scores via service
$userData   = UserService::getUserById($userId);
$userScores = UserService::getUserScores($userId);

$successMsg = "";
$errorMsg   = "";

// Handle email update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"])) {
    $newEmail = trim($_POST["email"]);

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format.";
    } else {
        if (UserService::updateEmail($userId, $newEmail)) {
            $successMsg = "Email updated successfully.";
            $userData["email"] = $newEmail;
        } else {
            $errorMsg = "Something went wrong updating your email.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Profile</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>

<div class="top-bar">
    <div>Logged in as: <?= htmlspecialchars($username) ?></div>
    <div class="top-right-controls">
        <button onclick="window.location='game.php'">Back to Game</button>
        <button onclick="window.location='change_password.php'">Change Password</button>
        <button onclick="window.location='logout.php'">Logout</button>
    </div>
</div>

<div class="profile-container">
    <h2>Your Profile</h2>

    <?php if ($successMsg): ?>
        <p class="success"><?= htmlspecialchars($successMsg) ?></p>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- LEFT: profile details -->
        <div class="profile-section">
            <h3>Basic Details</h3>
            <form method="post" class="profile-form">
                <label>Username</label>
                <input type="text" value="<?= htmlspecialchars($username) ?>" disabled>

                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>">

                <button type="submit">Update Email</button>

                <button type="button" onclick="window.location='change_password.php'">
                    Change Password
                </button>
            </form>
        </div>

        <!-- RIGHT: score history -->
        <div class="profile-section">
            <h3>Your Score History</h3>

            <?php if (empty($userScores)): ?>
                <p>You have no recorded scores yet.</p>
            <?php else: ?>
                <table class="scores-table">
                    <tr>
                        <th>Difficulty</th>
                        <th>Score</th>
                        <th>Time</th>
                        
                    </tr>
                    <?php foreach ($userScores as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['difficulty']) ?></td>
                            <td><?= $row['score'] ?></td>
                            <td><?= $row['time_taken'] ?>s</td>
                            
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
