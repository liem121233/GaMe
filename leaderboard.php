<?php
declare(strict_types=1);
require_once __DIR__ . '/../common.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = db()->query("SELECT name, floor_reached, victory, turns, created_at FROM leaderboard
                          ORDER BY victory DESC, floor_reached DESC, turns ASC LIMIT 10")->fetchAll();
    json_out(['entries' => $rows]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rename the most recent leaderboard entry for this session's last run (best-effort; no auth by design).
    $body = read_json_body();
    $name = trim((string)($body['name'] ?? ''));
    if ($name === '') json_out(['error' => 'Name required'], 400);
    $name = mb_substr($name, 0, 24);
    $last = db()->query("SELECT id FROM leaderboard ORDER BY id DESC LIMIT 1")->fetch();
    if ($last) {
        db()->prepare("UPDATE leaderboard SET name=? WHERE id=?")->execute([$name, $last['id']]);
    }
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
