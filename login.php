<?php
require_once "config.php";

// Read remembered username from cookie if present
$savedUsername = $_COOKIE['remember_username'] ?? "";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $remember = isset($_POST["remember_me"]);

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {
        $pdo = Database::getConnection();

        // Look up user by username
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row["password_hash"])) {
            // Login success → set session
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["username"] = $row["username"];

            // Remember me: store username in cookie (30 days)
            if ($remember) {
                setcookie(
                    "remember_username",
                    $row["username"],
                    time() + (30 * 24 * 60 * 60),
                    "/"
                );
            } else {
                // Clear cookie if unchecked
                setcookie("remember_username", "", time() - 3600, "/");
            }

            header("Location: game.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="auth-container">
    <h2>Login</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username</label>
        <input
            type="text"
            name="username"
            value="<?= htmlspecialchars($savedUsername) ?>"
            required
        >

        <label>Password</label>
        <div style="display:flex; gap:6px; align-items:center;">
            <input
                type="password"
                id="login_password"
                name="password"
                required
                style="flex:1;"
            >
            <button
                type="button"
                class="toggle-password"
                data-target="login_password"
                style="padding:6px 10px;"
            >
                Show
            </button>
        </div>

        <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
            <label style="font-size:0.9rem;">
                <input
                    type="checkbox"
                    name="remember_me"
                    <?= $savedUsername ? 'checked' : '' ?>
                >
                Remember me
            </label>

            <a href="forgot_password.php" style="font-size:0.9rem;">
                Forgot password?
            </a>
        </div>

        <button type="submit" style="margin-top:12px;">Login</button>
    </form>

    <p>Don’t have an account? <a href="register.php">Register</a></p>
</div>

<!-- Toggle password show/hide -->
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
