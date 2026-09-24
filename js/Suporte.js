// js/Suporte.js
; (function () {
  const REFRESH_INTERVAL_MS = 2000;
  const PING_INTERVAL_MS = 900;
  const STALE_THRESHOLD_S = 13;

  const PLACE_NAMES = {
    "113586179382037": "Lobby",
    "82893120277584": "Água‑Levada"
  };

  function formatUptime(diffSec) {
    const pct = Math.max(0, Math.min(100,
      Math.round(((STALE_THRESHOLD_S - diffSec) / STALE_THRESHOLD_S) * 100)
    ));
    return pct + '%';
  }

  function formatSessionDuration(seconds) {
    const min = Math.floor(seconds / 60);
    const sec = seconds % 60;
    return seconds < 60
      ? `${sec} seg`
      : `${min} min${sec > 0 ? ` ${sec} s` : ''}`;
  }

  function formatDateTimePT(dateString) {
    if (!dateString) return '—';
    try {
      const date = new Date(dateString);
      const utcDate = new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(),
        date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds());
      return utcDate.toLocaleString('pt-PT', {
        timeZone: 'Europe/Lisbon',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    } catch {
      return '—';
    }
  }

  async function loadGameStatus() {
    try {
      const container = document.getElementById('place-status-container');
      if (!container) return;

      const res = await fetch(`JSON/api/kr-status.json?t=${Date.now()}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      const nowSec = Date.now() / 1000;
      let globalTotal = 0;
      container.innerHTML = '';

      Object.entries(data.places || {}).forEach(([pid, place]) => {
        const name = PLACE_NAMES[pid] || `Place ${pid}`;

        let sumPlayers = 0, maxTs = 0, versions = [];
        for (const inst of Object.values(place.instances || {})) {
          sumPlayers += inst.playersOnline || 0;
          maxTs = Math.max(maxTs, inst.timestamp || 0);
          if (inst.version) versions.push(inst.version.replace(/^v/, ''));
        }

        const diffSec = nowSec - maxTs;
        const isFresh = diffSec < STALE_THRESHOLD_S;
        const players = isFresh ? sumPlayers : 0;
        globalTotal += players;

        const maintTs = Date.parse(place.nextMaintenance || '') || 0;
        const inMaintWindow = Date.now() >= maintTs && Date.now() < maintTs + 20 * 3600 * 1000;
        const state = inMaintWindow
          ? 'Manutenção'
          : (isFresh ? 'Online' : 'Offline');
        const icon = inMaintWindow ? '🟠' : (isFresh ? '🟢' : '🔴');

        const uptime = isFresh ? formatUptime(diffSec) : '0%';
        const version = versions.length
          ? 'v' + versions.sort((a, b) => versionCompare(a, b))[versions.length - 1]
          : '—';

        const avgSession = place.avgSessionDuration
          ? formatSessionDuration(place.avgSessionDuration)
          : '—';

        const maintDateStr = formatDateTimePT(place.nextMaintenance);

        const col = document.createElement('div');
        col.className = 'col-md-6';
        col.innerHTML = `
          <div class="card p-3 h-100 status-card">
            <h5 class="card-title mb-3">${name}</h5>
            <p class="mb-2">Status: <strong>${icon} ${state}</strong></p>
            <p class="mb-2">Versão: <strong>${version}</strong></p>
            <p class="mb-2">Jogadores: <strong>${players}</strong></p>
            <p class="mb-2">
              Taxa de Disponibilidade (%): 
              <strong>${uptime}</strong>
              <i class="bi bi-info-circle subtle-icon"
                 data-bs-toggle="tooltip"
                 title="Percentagem de tempo nos últimos ${STALE_THRESHOLD_S} s em que o servidor respondeu corretamente ao nosso status."></i>
            </p>
            <p class="mb-2">
              Tempo médio de permanência (24h): 
              <strong>${avgSession}</strong>
              ${place.avgSessionDuration ? `
              <i class="bi bi-info-circle subtle-icon"
                 data-bs-toggle="tooltip"
                 title="Média de duração das sessões dos jogadores."></i>` : ''}
            </p>
            <p class="mb-1">
              Próxima manutenção: <strong>${maintDateStr}</strong>
            </p>
          </div>`;
        container.appendChild(col);
      });

      const totalElem = document.getElementById('players-online');
      if (totalElem) {
        totalElem.textContent = globalTotal.toLocaleString('pt-PT');
      }

    } catch (err) {
      console.error('Erro ao carregar estado do jogo:', err);
    }
  }

  function versionCompare(a, b) {
    const pa = a.split('.').map(Number);
    const pb = b.split('.').map(Number);
    for (let i = 0; i < Math.max(pa.length, pb.length); i++) {
      if ((pa[i] || 0) > (pb[i] || 0)) return 1;
      if ((pa[i] || 0) < (pb[i] || 0)) return -1;
    }
    return 0;
  }

  async function updateBackendPing() {
    const el = document.getElementById('ping-backend');
    if (!el) return;
    const start = performance.now();
    try {
      await fetch('JSON/api/status.php', { method: 'HEAD', cache: 'no-store' });
      const latency = Math.round(performance.now() - start);
      el.textContent = `${latency} ms`;
    } catch {
      el.textContent = 'Falha no ping';
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    loadGameStatus();
    setInterval(loadGameStatus, REFRESH_INTERVAL_MS);

    updateBackendPing();
    setInterval(updateBackendPing, PING_INTERVAL_MS);

    document.querySelectorAll('[data-bs-toggle="tooltip"]')
      .forEach(el => new bootstrap.Tooltip(el));
  });
})();