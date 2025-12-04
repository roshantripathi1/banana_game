<?php
// banana_api.php
// JS → this file → BananaService → external API.

require_once "config.php";
require_once "services/BananaService.php";

header("Content-Type: application/json");

$result = BananaService::getPuzzle();

echo json_encode($result);
