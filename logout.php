<?php
// logout.php
// Logs user out and redirects to login.

require_once "config.php";

AuthService::logout();

header("Location: login.php");
exit;
