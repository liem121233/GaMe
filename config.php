<?php
// Ember Spire — database bootstrap
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dbFile = __DIR__ . '/game.db';
    $isNew = !file_exists($dbFile);

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // Always ensure schema exists (CREATE TABLE IF NOT EXISTS is idempotent).
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);

    return $pdo;
}

function json_out($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function session_key(): string {
    return session_id();
}
