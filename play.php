<?php
declare(strict_types=1);
require_once __DIR__ . '/../common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST required'], 405);

$body = read_json_body();
$runCardId = (int)($body['run_card_id'] ?? 0);

$run = get_run();
if (!$run) json_out(['error' => 'No active run'], 404);
$runId = (int)$run['id'];

if ($run['phase'] !== 'combat') json_out(['error' => 'Not in combat'], 400);

$st = db()->prepare("SELECT rc.*, c.* FROM run_cards rc JOIN card_catalog c ON c.card_key = rc.card_key
                      WHERE rc.id=? AND rc.run_id=? AND rc.zone='hand'");
$st->execute([$runCardId, $runId]);
$card = $st->fetch();
if (!$card) json_out(['error' => 'Card not in hand'], 400);

$energy = (int)$run['energy'];
$cost = (int)$card['cost'];
if ($energy < $cost) json_out(['error' => 'Not enough energy'], 400);

$enemy = get_enemy($runId);
if (!$enemy) json_out(['error' => 'No enemy present'], 400);

$playerHp = (int)$run['player_hp'];
$playerMax = (int)$run['player_max_hp'];
$playerBlock = (int)$run['block'];
$weakTurns = (int)$run['weak_turns'];

$enemyHp = (int)$enemy['hp'];
$enemyBlock = (int)$enemy['block'];
$enemyVuln = (int)$enemy['vulnerable_turns'];

$messages = [];

// -- Attack damage (possibly hitting multiple times) --------------------
if ($card['type'] === 'attack' && (int)$card['damage'] > 0 || $card['special'] === 'body_slam') {
    $hits = max(1, (int)$card['hits']);
    for ($i = 0; $i < $hits; $i++) {
        $base = $card['special'] === 'body_slam' ? $playerBlock : (int)$card['damage'];
        if ($weakTurns > 0) $base = (int)floor($base * 0.75);
        if ($enemyVuln > 0) $base = (int)ceil($base * 1.5);
        $absorb = min($enemyBlock, $base);
        $enemyBlock -= $absorb;
        $remaining = $base - $absorb;
        $enemyHp = max(0, $enemyHp - $remaining);
    }
    $messages[] = "You strike the {$enemy['name']} with {$card['name']}.";
}

// -- Block ---------------------------------------------------------------
if ((int)$card['block'] > 0) {
    $playerBlock += (int)$card['block'];
    $messages[] = "You gain {$card['block']} Block.";
}

// -- Heal ------------------------------------------------------------------
if ((int)$card['heal'] > 0) {
    $playerHp = min($playerMax, $playerHp + (int)$card['heal']);
    $messages[] = "You recover {$card['heal']} HP.";
}

// -- Vulnerable applied to enemy -------------------------------------------
if ((int)$card['apply_vulnerable'] > 0) {
    $enemyVuln += (int)$card['apply_vulnerable'];
    $messages[] = "The {$enemy['name']} is left Vulnerable.";
}

// -- Draw --------------------------------------------------------------------
if ((int)$card['draw_cards'] > 0) {
    // temporarily commit hand/energy state isn't needed for draw
}

// -- commit card movement & costs --------------------------------------------
db()->prepare("UPDATE run_cards SET zone='discard' WHERE id=?")->execute([$runCardId]);
$energy -= $cost;

if ((int)$card['draw_cards'] > 0) {
    draw_cards($runId, (int)$card['draw_cards']);
}

update_run($runId, ['player_hp' => $playerHp, 'block' => $playerBlock, 'energy' => $energy]);
db()->prepare("UPDATE enemy_state SET hp=?, block=?, vulnerable_turns=? WHERE run_id=?")
    ->execute([$enemyHp, $enemyBlock, $enemyVuln, $runId]);

// -- Check victory over this enemy -------------------------------------------
if ($enemyHp <= 0) {
    $floorIdx = (int)$run['floor_index'];
    $floorDef = FLOORS[$floorIdx] ?? end(FLOORS);
    $gold = (int)$run['gold'] + rand(10, 20);

    if ($floorDef['type'] === 'boss') {
        update_run($runId, [
            'phase' => 'victory', 'status' => 'victory', 'gold' => $gold,
            'last_message' => "You have defeated {$enemy['name']}! The Ember Spire falls silent.",
        ]);
        db()->prepare("INSERT INTO leaderboard (name, floor_reached, victory, turns) VALUES (?,?,1,?)")
            ->execute(['Adventurer', count(FLOORS), (int)$run['turn_number']]);
    } else {
        $options = REWARD_POOL;
        shuffle($options);
        $picked = array_slice($options, 0, 3);
        update_run($runId, [
            'phase' => 'reward', 'gold' => $gold,
            'reward_options' => json_encode($picked),
            'last_message' => "You have defeated the {$enemy['name']}! Choose a card to keep.",
        ]);
    }
} else {
    save_message($runId, implode(' ', $messages));
}

json_out(fresh_state_response());
