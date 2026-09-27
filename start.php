<?php
declare(strict_types=1);
require_once __DIR__ . '/../common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST required'], 405);

$sid = session_key();

// Clean up any previous active run for this browser session.
$old = db()->prepare("SELECT id FROM runs WHERE session_id=? AND status='active'");
$old->execute([$sid]);
foreach ($old->fetchAll() as $r) {
    $rid = (int)$r['id'];
    db()->prepare("DELETE FROM run_cards WHERE run_id=?")->execute([$rid]);
    db()->prepare("DELETE FROM enemy_state WHERE run_id=?")->execute([$rid]);
    db()->prepare("UPDATE runs SET status='abandoned' WHERE id=?")->execute([$rid]);
}

$st = db()->prepare("INSERT INTO runs (session_id, player_hp, player_max_hp, energy, energy_max, floor_index, phase, turn_number, status, last_message)
                      VALUES (?, 70, 70, 3, 3, 0, 'combat', 1, 'active', 'You descend into the Ember Spire.')");
$st->execute([$sid]);
$runId = (int)db()->lastInsertId();

seed_starter_deck($runId);
spawn_enemy($runId, FLOORS[0]['enemy']);
draw_cards($runId, HAND_SIZE);

$run = get_run();
json_out(build_state($run));
