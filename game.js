(() => {
  const API = 'api/';
  let state = null;

  const el = (id) => document.getElementById(id);

  const screens = {
    start: el('screen-start'),
    game: el('screen-game'),
  };
  const overlays = {
    reward: el('overlay-reward'),
    rest: el('overlay-rest'),
    gameover: el('overlay-gameover'),
    victory: el('overlay-victory'),
  };

  function hideAllOverlays() {
    Object.values(overlays).forEach(o => o.classList.add('hidden'));
  }

  async function api(path, body) {
    const opts = body
      ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }
      : { method: 'GET' };
    const res = await fetch(API + path, opts);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
  }

  // --------------------------------------------------------------- render

  function render() {
    if (!state || state.status === 'none') {
      screens.start.classList.remove('hidden');
      screens.game.classList.add('hidden');
      hideAllOverlays();
      return;
    }

    screens.start.classList.add('hidden');
    screens.game.classList.remove('hidden');
    hideAllOverlays();

    el('floor-label').textContent = state.floor_label;
    el('gold-amount').textContent = state.gold;
    el('turn-number').textContent = state.turn_number;
    el('log-panel').textContent = state.message || '';

    renderPlayer();
    renderEnemy();
    renderHand();

    if (state.phase === 'reward') renderReward();
    else if (state.phase === 'rest') overlays.rest.classList.remove('hidden');
    else if (state.phase === 'game_over') renderGameOver();
    else if (state.phase === 'victory') renderVictory();
  }

  function renderPlayer() {
    const p = state.player;
    const pct = Math.max(0, Math.min(100, (p.hp / p.max_hp) * 100));
    el('player-hp-fill').style.width = pct + '%';
    el('player-hp-text').textContent = `${p.hp}/${p.max_hp}`;
    el('player-block-badge').textContent = `🛡 ${p.block}`;
    const weakBadge = el('player-weak-badge');
    if (p.weak_turns > 0) { weakBadge.textContent = `☁ ضعف ${p.weak_turns}`; weakBadge.classList.remove('hidden'); }
    else weakBadge.classList.add('hidden');

    el('energy-current').textContent = p.energy;
    el('energy-max').textContent = p.energy_max;

    el('draw-count').textContent = state.deck_counts.draw;
    el('discard-count').textContent = state.deck_counts.discard;
  }

  function renderEnemy() {
    const block = el('enemy-block');
    const e = state.enemy;
    if (!e) { block.style.visibility = 'hidden'; return; }
    block.style.visibility = 'visible';

    el('enemy-name').textContent = e.name;
    const pct = Math.max(0, Math.min(100, (e.hp / e.max_hp) * 100));
    el('enemy-hp-fill').style.width = pct + '%';
    el('enemy-hp-text').textContent = `${e.hp}/${e.max_hp}`;
    el('enemy-block-badge').textContent = `🛡 ${e.block}`;

    const vulnBadge = el('enemy-vuln-badge');
    if (e.vulnerable_turns > 0) { vulnBadge.textContent = `🎯 هش ${e.vulnerable_turns}`; vulnBadge.classList.remove('hidden'); }
    else vulnBadge.classList.add('hidden');

    const strBadge = el('enemy-str-badge');
    if (e.strength > 0) { strBadge.textContent = `💪 ${e.strength}`; strBadge.classList.remove('hidden'); }
    else strBadge.classList.add('hidden');

    el('enemy-intent').textContent = e.intent;
  }

  function cardTypeLabel(t) { return t === 'attack' ? 'هجوم' : 'مهارة'; }

  function makeCardEl(card, opts = {}) {
    const div = document.createElement('div');
    div.className = 'card';
    if (opts.unaffordable) div.classList.add('unaffordable');
    div.innerHTML = `
      <div class="card-cost">${card.cost}</div>
      <div class="card-type">${cardTypeLabel(card.type)}</div>
      <div class="card-name">${card.name}</div>
      <div class="card-desc">${card.description}</div>
    `;
    return div;
  }

  function renderHand() {
    const hand = el('hand');
    hand.innerHTML = '';
    const inCombat = state.phase === 'combat';
    state.hand.forEach(card => {
      const unaffordable = inCombat && card.cost > state.player.energy;
      const cardEl = makeCardEl(card, { unaffordable });
      if (inCombat && !unaffordable) {
        cardEl.addEventListener('click', () => playCard(card.run_card_id));
      }
      hand.appendChild(cardEl);
    });
  }

  function renderReward() {
    const wrap = el('reward-options');
    wrap.innerHTML = '';
    (state.reward_options || []).forEach(card => {
      const cardEl = makeCardEl(card);
      cardEl.addEventListener('click', () => advance('pick_card', card.card_key));
      wrap.appendChild(cardEl);
    });
    overlays.reward.classList.remove('hidden');
  }

  function renderGameOver() {
    el('gameover-message').textContent = state.message || 'انتهت رحلتك هنا.';
    overlays.gameover.classList.remove('hidden');
  }

  function renderVictory() {
    el('victory-message').textContent = state.message || 'لقد أتممت الصعود!';
    overlays.victory.classList.remove('hidden');
  }

  // ---------------------------------------------------------------- actions

  async function refresh() {
    state = await api('state.php');
    render();
  }

  async function startRun() {
    state = await api('start.php', {});
    render();
  }

  async function playCard(runCardId) {
    try {
      state = await api('play.php', { run_card_id: runCardId });
      render();
    } catch (e) { console.error(e); }
  }

  async function endTurn() {
    try {
      state = await api('end_turn.php', {});
      render();
    } catch (e) { console.error(e); }
  }

  async function advance(action, cardKey) {
    try {
      const body = { action };
      if (cardKey) body.card_key = cardKey;
      state = await api('advance.php', body);
      render();
    } catch (e) { console.error(e); }
  }

  async function loadLeaderboard() {
    try {
      const data = await api('leaderboard.php');
      const list = el('leaderboard-list');
      list.innerHTML = '';
      if (!data.entries || data.entries.length === 0) {
        list.innerHTML = '<li class="lb-empty">لا توجد سجلات بعد. كن أول من يصعد.</li>';
        return;
      }
      data.entries.forEach(entry => {
        const li = document.createElement('li');
        if (entry.victory) li.classList.add('lb-victory');
        const status = entry.victory ? 'انتصار' : `سقط عند الطابق ${entry.floor_reached}`;
        li.innerHTML = `<span>${escapeHtml(entry.name)}</span><span>${status} · ${entry.turns} دورة</span>`;
        list.appendChild(li);
      });
    } catch (e) { console.error(e); }
  }

  function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  async function submitScore(inputId) {
    const input = el(inputId);
    const name = input.value.trim();
    if (!name) return;
    try {
      await api('leaderboard.php', { name });
      input.value = '';
      input.disabled = true;
      loadLeaderboard();
    } catch (e) { console.error(e); }
  }

  // ----------------------------------------------------------------- wire up

  el('btn-begin').addEventListener('click', startRun);
  el('btn-end-turn').addEventListener('click', endTurn);
  el('btn-skip-reward').addEventListener('click', () => advance('skip'));
  el('btn-rest').addEventListener('click', () => advance('rest'));
  el('btn-submit-defeat').addEventListener('click', () => submitScore('input-name-defeat'));
  el('btn-submit-victory').addEventListener('click', () => submitScore('input-name-victory'));
  el('btn-restart-defeat').addEventListener('click', startRun);
  el('btn-restart-victory').addEventListener('click', startRun);

  // ------------------------------------------------------------------- init

  (async function init() {
    loadLeaderboard();
    try {
      await refresh();
    } catch (e) {
      state = { status: 'none' };
      render();
    }
  })();
})();
