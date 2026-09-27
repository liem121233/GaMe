<?php
// Ember Spire — shared game logic
declare(strict_types=1);
require_once __DIR__ . '/config.php';

// ---------------------------------------------------------------- constants

const FLOORS = [
    ['type' => 'combat', 'enemy' => 'rat',     'label' => 'The Damp Cellar'],
    ['type' => 'combat', 'enemy' => 'fungal',  'label' => 'The Spore Grove'],
    ['type' => 'rest',   'enemy' => null,      'label' => 'A Quiet Landing'],
    ['type' => 'combat', 'enemy' => 'bandit',  'label' => 'The Broken Bridge'],
    ['type' => 'elite',  'enemy' => 'golem',   'label' => 'The Sealed Vault'],
    ['type' => 'boss',   'enemy' => 'hollow_king', 'label' => 'The Ember Throne'],
];

const ENEMIES = [
    'rat' => [
        'name' => 'Cave Rat', 'hp' => 20,
        'pattern' => [
            ['type' => 'attack', 'value' => 6],
            ['type' => 'attack', 'value' => 6],
            ['type' => 'defend', 'value' => 5],
        ],
    ],
    'fungal' => [
        'name' => 'Fungal Beast', 'hp' => 28,
        'pattern' => [
            ['type' => 'attack', 'value' => 7],
            ['type' => 'buff_strength', 'value' => 2],
            ['type' => 'attack', 'value' => 9],
        ],
    ],
    'bandit' => [
        'name' => 'Bridge Bandit', 'hp' => 34,
        'pattern' => [
            ['type' => 'attack', 'value' => 10],
            ['type' => 'defend', 'value' => 8],
            ['type' => 'attack', 'value' => 10],
        ],
    ],
    'golem' => [
        'name' => 'Vault Golem', 'hp' => 46,
        'pattern' => [
            ['type' => 'attack', 'value' => 14],
            ['type' => 'debuff_weak', 'value' => 2],
            ['type' => 'attack', 'value' => 16],
        ],
    ],
    'hollow_king' => [
        'name' => 'The Hollow King', 'hp' => 80,
        'pattern' => [
            ['type' => 'attack', 'value' => 15],
            ['type' => 'buff_strength', 'value' => 3],
            ['type' => 'attack', 'value' => 12],
            ['type' => 'attack', 'value' => 22],
        ],
    ],
];

const STARTER_DECK = [
    'strike', 'strike', 'strike', 'strike', 'strike',
    'defend', 'defend', 'defend', 'defend',
    'bash',
];

const REWARD_POOL = [
    'cleave', 'iron_wave', 'body_slam', 'shrug_it_off',
    'pommel_strike', 'flourish', 'twin_strike', 'vulnerable_shot', 'bash',
];

const HAND_SIZE = 5;

// ------------------------------------------------------------- card catalog

function all_cards(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $rows = db()->query('SELECT * FROM card_catalog')->fetchAll();
    $cache = [];
    foreach ($rows as $r) $cache[$r['card_key']] = $r;
    return $cache;
}

function card(string $key): array {
    $cards = all_cards();
    if (!isset($cards[$key])) throw new RuntimeException("Unknown card: $key");
    return $cards[$key];
}

// ------------------------------------------------------------------- deck

function draw_pile_count(int $runId): int {
    $st = db()->prepare("SELECT COUNT(*) c FROM run_cards WHERE run_id=? AND zone='draw'");
    $st->execute([$runId]);
    return (int)$st->fetch()['c'];
}

function reshuffle_if_needed(int $runId): void {
    if (draw_pile_count($runId) > 0) return;
    db()->prepare("UPDATE run_cards SET zone='draw' WHERE run_id=? AND zone='discard'")->execute([$runId]);
}

function draw_cards(int $runId, int $n): void {
    for ($i = 0; $i < $n; $i++) {
        reshuffle_if_needed($runId);
        $st = db()->prepare("SELECT id FROM run_cards WHERE run_id=? AND zone='draw' ORDER BY RANDOM() LIMIT 1");
        $st->execute([$runId]);
        $row = $st->fetch();
        if (!$row) break; // deck totally empty (shouldn't happen)
        db()->prepare("UPDATE run_cards SET zone='hand' WHERE id=?")->execute([$row['id']]);
    }
}

function discard_hand(int $runId): void {
    db()->prepare("UPDATE run_cards SET zone='discard' WHERE run_id=? AND zone='hand'")->execute([$runId]);
}

function seed_starter_deck(int $runId): void {
    $st = db()->prepare("INSERT INTO run_cards (run_id, card_key, zone) VALUES (?,?, 'draw')");
    foreach (STARTER_DECK as $key) $st->execute([$runId, $key]);
}

function add_card_to_deck(int $runId, string $key): void {
    db()->prepare("INSERT INTO run_cards (run_id, card_key, zone) VALUES (?,?, 'discard')")->execute([$runId, $key]);
}

// ------------------------------------------------------------------ enemy

function spawn_enemy(int $runId, string $enemyKey): void {
    $tpl = ENEMIES[$enemyKey];
    db()->prepare("DELETE FROM enemy_state WHERE run_id=?")->execute([$runId]);
    $st = db()->prepare("INSERT INTO enemy_state (run_id, enemy_key, name, hp, max_hp, block, strength, vulnerable_turns, pattern_index)
                          VALUES (?,?,?,?,?,0,0,0,0)");
    $st->execute([$runId, $enemyKey, $tpl['name'], $tpl['hp'], $tpl['hp']]);
}

function get_enemy(int $runId): ?array {
    $st = db()->prepare("SELECT * FROM enemy_state WHERE run_id=?");
    $st->execute([$runId]);
    $row = $st->fetch();
    return $row ?: null;
}

function enemy_intent_text(array $enemy): string {
    $tpl = ENEMIES[$enemy['enemy_key']];
    $pattern = $tpl['pattern'];
    $step = $pattern[$enemy['pattern_index'] % count($pattern)];
    switch ($step['type']) {
        case 'attack':
            $dmg = $step['value'] + (int)$enemy['strength'];
            return "Winding up to strike for $dmg";
        case 'defend':
            return "Bracing for impact (+{$step['value']} Block)";
        case 'buff_strength':
            return "Channeling power (+{$step['value']} Strength)";
        case 'debuff_weak':
            return "Preparing a hex (Weaken {$step['value']})";
        default:
            return "Stirring...";
    }
}

// ------------------------------------------------------------------- run

function get_run(): ?array {
    // Returns the most recent run for this session regardless of status, so a
    // just-finished run's victory/defeat screen still has state to render.
    $sid = session_key();
    $st = db()->prepare("SELECT * FROM runs WHERE session_id=? ORDER BY id DESC LIMIT 1");
    $st->execute([$sid]);
    $row = $st->fetch() ?: null;
    if ($row && $row['status'] === 'abandoned') return null;
    return $row;
}

function update_run(int $runId, array $fields): void {
    $sets = [];
    $vals = [];
    foreach ($fields as $k => $v) { $sets[] = "$k=?"; $vals[] = $v; }
    $sets[] = "updated_at=CURRENT_TIMESTAMP";
    $vals[] = $runId;
    db()->prepare("UPDATE runs SET " . implode(',', $sets) . " WHERE id=?")->execute($vals);
}

function floor_label(int $idx): string {
    if ($idx >= count(FLOORS)) return 'The Ember Throne';
    $f = FLOORS[$idx];
    $n = $idx + 1;
    $total = count(FLOORS);
    return "Floor $n / $total — {$f['label']}";
}

// ----------------------------------------------------------- state export

function build_state(array $run): array {
    $runId = (int)$run['id'];
    $hand = db()->prepare("SELECT rc.id as run_card_id, c.* FROM run_cards rc
                            JOIN card_catalog c ON c.card_key = rc.card_key
                            WHERE rc.run_id=? AND rc.zone='hand' ORDER BY rc.id");
    $hand->execute([$runId]);
    $handRows = $hand->fetchAll();

    $counts = db()->prepare("SELECT zone, COUNT(*) c FROM run_cards WHERE run_id=? GROUP BY zone");
    $counts->execute([$runId]);
    $zoneCounts = ['draw' => 0, 'hand' => 0, 'discard' => 0, 'exhaust' => 0];
    foreach ($counts->fetchAll() as $r) $zoneCounts[$r['zone']] = (int)$r['c'];

    $enemyOut = null;
    if ($run['phase'] === 'combat') {
        $enemy = get_enemy($runId);
        if ($enemy) {
            $enemyOut = [
                'name' => $enemy['name'],
                'hp' => (int)$enemy['hp'],
                'max_hp' => (int)$enemy['max_hp'],
                'block' => (int)$enemy['block'],
                'strength' => (int)$enemy['strength'],
                'vulnerable_turns' => (int)$enemy['vulnerable_turns'],
                'intent' => enemy_intent_text($enemy),
            ];
        }
    }

    $rewardOptions = null;
    if ($run['phase'] === 'reward' && $run['reward_options']) {
        $keys = json_decode($run['reward_options'], true);
        $rewardOptions = array_map(fn($k) => card($k), $keys);
    }

    $floorIdx = (int)$run['floor_index'];
    $floorType = $floorIdx < count(FLOORS) ? FLOORS[$floorIdx]['type'] : 'boss';

    return [
        'status' => $run['status'],
        'phase' => $run['phase'],
        'floor_index' => $floorIdx,
        'floor_total' => count(FLOORS),
        'floor_type' => $floorType,
        'floor_label' => floor_label($floorIdx),
        'turn_number' => (int)$run['turn_number'],
        'gold' => (int)$run['gold'],
        'player' => [
            'hp' => (int)$run['player_hp'],
            'max_hp' => (int)$run['player_max_hp'],
            'block' => (int)$run['block'],
            'energy' => (int)$run['energy'],
            'energy_max' => (int)$run['energy_max'],
            'weak_turns' => (int)$run['weak_turns'],
        ],
        'hand' => $handRows,
        'deck_counts' => $zoneCounts,
        'enemy' => $enemyOut,
        'reward_options' => $rewardOptions,
        'message' => $run['last_message'],
    ];
}

function fresh_state_response(): array {
    $run = get_run();
    if (!$run) return ['status' => 'none'];
    return build_state($run);
}

function save_message(int $runId, string $msg): void {
    update_run($runId, ['last_message' => $msg]);
}

function clamp(int $v, int $min, int $max): int {
    return max($min, min($max, $v));
}
