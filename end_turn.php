<?php
declare(strict_types=1);
require_once __DIR__ . '/../common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST required'], 405);

$run = get_run();
if (!$run) json_out(['error' => 'No active run'], 404);
$runId = (int)$run['id'];

if ($run['phase'] !== 'combat') json_out(['error' => 'Not in combat'], 400);

$enemy = get_enemy($runId);
if (!$enemy) json_out(['error' => 'No enemy present'], 400);

$tpl = ENEMIES[$enemy['enemy_key']];
$pattern = $tpl['pattern'];
$step = $pattern[$enemy['pattern_index'] % count($pattern)];

// 1. Start of enemy's turn: clear its stale block, then act.
$enemyBlock = 0;
$enemyStrength = (int)$enemy['strength'];
$playerHp = (int)$run['player_hp'];
$playerBlock = (int)$run['block'];
$weakTurns = (int)$run['weak_turns'];
$messages = [];

switch ($step['type']) {
    case 'attack':
        $dmg = (int)$step['value'] + $enemyStrength;
        $absorb = min($playerBlock, $dmg);
        $playerBlock -= $absorb;
        $remaining = $dmg - $absorb;
        $playerHp = max(0, $playerHp - $remaining);
        $messages[] = "The {$enemy['name']} attacks for $dmg damage.";
        break;
    case 'defend':
        $enemyBlock += (int)$step['value'];
        $messages[] = "The {$enemy['name']} braces itself.";
        break;
    case 'buff_strength':
        $enemyStrength += (int)$step['value'];
        $messages[] = "The {$enemy['name']} grows stronger.";
        break;
    case 'debuff_weak':
        $weakTurns += (int)$step['value'];
        $messages[] = "The {$enemy['name']} weakens your resolve.";
        break;
}

$newPatternIndex = ($enemy['pattern_index'] + 1) % count($pattern);
db()->prepare("UPDATE enemy_state SET block=?, strength=?, pattern_index=? WHERE run_id=?")
    ->execute([$enemyBlock, $enemyStrength, $newPatternIndex, $runId]);

// 2. Check for player death.
if ($playerHp <= 0) {
    update_run($runId, [
        'player_hp' => 0, 'block' => 0, 'phase' => 'game_over', 'status' => 'defeat',
        'last_message' => "You fall to the {$enemy['name']}. The Spire claims another soul.",
    ]);
    db()->prepare("INSERT INTO leaderboard (name, floor_reached, victory, turns) VALUES (?,?,0,?)")
        ->execute(['Adventurer', (int)$run['floor_index'] + 1, (int)$run['turn_number']]);
    json_out(fresh_state_response());
}

// 3. Start of player's new turn: clear block, tick down weak, discard & draw.
if ($weakTurns > 0) $weakTurns--;

discard_hand($runId);
draw_cards($runId, HAND_SIZE);

update_run($runId, [
    'player_hp' => $playerHp,
    'block' => 0,
    'weak_turns' => $weakTurns,
    'energy' => (int)$run['energy_max'],
    'turn_number' => (int)$run['turn_number'] + 1,
    'last_message' => implode(' ', $messages),
]);

json_out(fresh_state_response());
