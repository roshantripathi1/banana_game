<?php
// change_password.php
// Lets user change their password.

require_once "config.php";

// Only logged users allowed
AuthService::requireLogin();

$userId   = AuthService::getUserId();
$username = AuthService::getUsername();

$successMsg = "";
$errorMsg   = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current = $_POST["current_password"] ?? "";
    $new     = $_POST["new_password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if ($current === "" || $new === "" || $confirm === "") {
        $errorMsg = "All fields are required.";
    } elseif ($new !== $confirm) {
        $errorMsg = "New password and confirm password do not match.";
    } elseif (strlen($new) < 6) {
        $errorMsg = "New password must be at least 6 characters.";
    } else {
        // Check old password
        if (!UserService::verifyPassword($userId, $current)) {
            $errorMsg = "Current password is incorrect.";
        } else {
            // Save new password
            if (UserService::updatePassword($userId, $new)) {
                $successMsg = "Password updated successfully.";
            } else {
                $errorMsg = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>

<div class="top-bar">
    <div>Logged in as: <?= htmlspecialchars($username) ?></div>
    <div class="top-right-controls">
        <button onclick="window.location='profile.php'">Back to Profile</button>
        <button onclick="window.location='game.php'">Back to Game</button>
        <button onclick="window.location='logout.php'">Logout</button>
    </div>
</div>

<div class="change-password-container">
    <h2>Change Password</h2>

    <?php if ($successMsg): ?>
        <p class="success"><?= htmlspecialchars($successMsg) ?></p>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Update Password</button>
    </form>
</div>

</body>
</html>
