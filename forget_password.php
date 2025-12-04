<?php
require_once "config.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $newPass  = $_POST["new_password"] ?? "";
    $confirm  = $_POST["confirm_password"] ?? "";

    if ($username === "" || $email === "" || $newPass === "" || $confirm === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($newPass !== $confirm) {
        $error = "New password and confirm password do not match.";
    } elseif (strlen($newPass) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Find user by username + email
        $user = UserService::getUserByUsernameAndEmail($username, $email);

        if (!$user) {
            $error = "User not found with that username and email.";
        } else {
            // Update password
            if (UserService::updatePassword((int)$user['id'], $newPass)) {
                $success = "Password reset successfully. You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="auth-container">
    <h2>Forgot Password</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>New Password</label>
        <div style="display:flex; gap:6px; align-items:center;">
            <input
                type="password"
                id="fp_password"
                name="new_password"
                required
                style="flex:1;"
            >
            <button
                type="button"
                class="toggle-password"
                data-target="fp_password"
                style="padding:6px 10px;"
            >
                Show
            </button>
        </div>

        <label>Confirm New Password</label>
        <div style="display:flex; gap:6px; align-items:center;">
            <input
                type="password"
                id="fp_confirm"
                name="confirm_password"
                required
                style="flex:1;"
            >
            <button
                type="button"
                class="toggle-password"
                data-target="fp_confirm"
                style="padding:6px 10px;"
            >
                Show
            </button>
        </div>

        <button type="submit" style="margin-top:12px;">Reset Password</button>
    </form>

    <p><a href="login.php">Back to login</a></p>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = 'Hide';
        } else {
            input.type = 'password';
            btn.textContent = 'Show';
        }
    });
});
</script>
</body>
</html>
