<?php
require_once "config.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm"] ?? "";

    if ($username === "" || $email === "" || $password === "" || $confirm === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $pdo = Database::getConnection();

        // Check if username already taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Username already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hash]);
            $success = "Registration successful. You can now login.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="auth-container">
    <h2>Register</h2>

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

        <label>Password</label>
        <div style="display:flex; gap:6px; align-items:center;">
            <input
                type="password"
                id="reg_password"
                name="password"
                required
                style="flex:1;"
            >
            <button
                type="button"
                class="toggle-password"
                data-target="reg_password"
                style="padding:6px 10px;"
            >
                Show
            </button>
        </div>

        <label>Confirm Password</label>
        <div style="display:flex; gap:6px; align-items:center;">
            <input
                type="password"
                id="reg_confirm"
                name="confirm"
                required
                style="flex:1;"
            >
            <button
                type="button"
                class="toggle-password"
                data-target="reg_confirm"
                style="padding:6px 10px;"
            >
                Show
            </button>
        </div>

        <button type="submit" style="margin-top:12px;">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
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
