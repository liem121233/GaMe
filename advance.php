<?php
declare(strict_types=1);
require_once __DIR__ . '/../common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST required'], 405);

$body = read_json_body();
$action = $body['action'] ?? '';

$run = get_run();
if (!$run) json_out(['error' => 'No active run'], 404);
$runId = (int)$run['id'];
$phase = $run['phase'];

if ($phase === 'reward') {
    if (!in_array($action, ['pick_card', 'skip'], true)) json_out(['error' => 'Invalid action for reward phase'], 400);
    if ($action === 'pick_card') {
        $cardKey = (string)($body['card_key'] ?? '');
        $options = json_decode($run['reward_options'] ?? '[]', true) ?: [];
        if (!in_array($cardKey, $options, true)) json_out(['error' => 'Card not offered'], 400);
        add_card_to_deck($runId, $cardKey);
    }
} elseif ($phase === 'rest') {
    if ($action !== 'rest') json_out(['error' => 'Invalid action for rest phase'], 400);
    $healed = (int)round((int)$run['player_max_hp'] * 0.3);
    $newHp = min((int)$run['player_max_hp'], (int)$run['player_hp'] + $healed);
    update_run($runId, ['player_hp' => $newHp]);
} else {
    json_out(['error' => 'Nothing to advance from here'], 400);
}

$nextIndex = (int)$run['floor_index'] + 1;
$next = FLOORS[$nextIndex] ?? null;

if ($next === null) {
    // Should not normally happen (boss never routes here), but guard anyway.
    update_run($runId, ['phase' => 'victory', 'status' => 'victory', 'floor_index' => $nextIndex]);
    json_out(fresh_state_response());
}

$fields = ['floor_index' => $nextIndex, 'reward_options' => null];

if ($next['type'] === 'rest') {
    $fields['phase'] = 'rest';
    $fields['last_message'] = 'A moment of quiet. Rest here to recover your strength.';
} else {
    $fields['phase'] = 'combat';
    $fields['last_message'] = "You enter {$next['label']}.";
}

update_run($runId, $fields);

if ($next['type'] !== 'rest') {
    spawn_enemy($runId, $next['enemy']);
    discard_hand($runId);
    draw_cards($runId, HAND_SIZE);
    update_run($runId, ['block' => 0, 'energy' => (int)$run['energy_max']]);
}

json_out(fresh_state_response());
