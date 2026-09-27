-- Ember Spire — schema (SQLite dialect, works with PDO sqlite)

CREATE TABLE IF NOT EXISTS card_catalog (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    card_key          TEXT UNIQUE NOT NULL,
    name              TEXT NOT NULL,
    cost              INTEGER NOT NULL DEFAULT 1,
    type              TEXT NOT NULL,              -- 'attack' | 'skill'
    damage            INTEGER NOT NULL DEFAULT 0,
    block             INTEGER NOT NULL DEFAULT 0,
    heal              INTEGER NOT NULL DEFAULT 0,
    draw_cards        INTEGER NOT NULL DEFAULT 0,
    apply_vulnerable  INTEGER NOT NULL DEFAULT 0,
    hits              INTEGER NOT NULL DEFAULT 1,
    special           TEXT,
    description       TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS runs (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id        TEXT NOT NULL,
    player_hp         INTEGER NOT NULL DEFAULT 70,
    player_max_hp     INTEGER NOT NULL DEFAULT 70,
    block             INTEGER NOT NULL DEFAULT 0,
    energy            INTEGER NOT NULL DEFAULT 3,
    energy_max        INTEGER NOT NULL DEFAULT 3,
    gold              INTEGER NOT NULL DEFAULT 0,
    weak_turns        INTEGER NOT NULL DEFAULT 0,
    floor_index       INTEGER NOT NULL DEFAULT 0,
    phase             TEXT NOT NULL DEFAULT 'combat',   -- combat | reward | rest | victory | game_over
    turn_number       INTEGER NOT NULL DEFAULT 1,
    reward_options    TEXT,
    last_message      TEXT,
    status            TEXT NOT NULL DEFAULT 'active',   -- active | victory | defeat
    created_at        TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at        TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS run_cards (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    run_id      INTEGER NOT NULL,
    card_key    TEXT NOT NULL,
    zone        TEXT NOT NULL DEFAULT 'draw'   -- draw | hand | discard | exhaust
);

CREATE TABLE IF NOT EXISTS enemy_state (
    run_id          INTEGER PRIMARY KEY,
    enemy_key       TEXT NOT NULL,
    name            TEXT NOT NULL,
    hp              INTEGER NOT NULL,
    max_hp          INTEGER NOT NULL,
    block           INTEGER NOT NULL DEFAULT 0,
    strength        INTEGER NOT NULL DEFAULT 0,
    vulnerable_turns INTEGER NOT NULL DEFAULT 0,
    pattern_index   INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS leaderboard (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    name           TEXT NOT NULL,
    floor_reached  INTEGER NOT NULL,
    victory        INTEGER NOT NULL DEFAULT 0,
    turns          INTEGER NOT NULL DEFAULT 0,
    created_at     TEXT DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO card_catalog (card_key,name,cost,type,damage,block,heal,draw_cards,apply_vulnerable,hits,special,description) VALUES
('strike',        'Strike',        1,'attack',6,0,0,0,0,1,NULL,'Deal 6 damage.'),
('defend',        'Defend',        1,'skill', 0,5,0,0,0,1,NULL,'Gain 5 Block.'),
('bash',          'Bash',          2,'attack',8,0,0,0,2,1,NULL,'Deal 8 damage. Apply 2 Vulnerable.'),
('cleave',        'Cleave',        1,'attack',8,0,0,0,0,1,NULL,'Deal 8 damage.'),
('iron_wave',     'Iron Wave',     1,'attack',5,5,0,0,0,1,NULL,'Deal 5 damage. Gain 5 Block.'),
('body_slam',     'Body Slam',     1,'attack',0,0,0,0,0,1,'body_slam','Deal damage equal to your Block.'),
('shrug_it_off',  'Shrug It Off',  1,'skill', 0,8,0,1,0,1,NULL,'Gain 8 Block. Draw 1 card.'),
('pommel_strike', 'Pommel Strike', 1,'attack',9,0,0,1,0,1,NULL,'Deal 9 damage. Draw 1 card.'),
('flourish',      'Flourish',      2,'skill', 0,0,6,0,0,1,NULL,'Heal 6 HP.'),
('twin_strike',   'Twin Strike',   1,'attack',5,0,0,0,0,2,NULL,'Deal 5 damage twice.'),
('vulnerable_shot','Vulnerable Shot',1,'attack',6,0,0,0,2,1,NULL,'Deal 6 damage. Apply 2 Vulnerable.');
