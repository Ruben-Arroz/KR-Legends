// public_html/KRLegends/js/pwa_contador.js
// Versão corrigida — força rede para a contagem e adiciona ping first_open

// === Base da API detectada automaticamente ===
const apiBase =
  document.currentScript?.dataset.apiBase ||
  (document.querySelector('script[data-api-base]')?.dataset.apiBase) ||
  './api';

// --- util: gera device id (UUID v4 hex compact) ---
function generateDeviceId() {
  const buf = new Uint8Array(16);
  crypto.getRandomValues(buf);
  buf[6] = (buf[6] & 0x0f) | 0x40;
  buf[8] = (buf[8] & 0x3f) | 0x80;
  return [...buf].map(b => b.toString(16).padStart(2, '0')).join('');
}

function getDeviceId() {
  let id = localStorage.getItem('kr_pwa_device_id');
  if (!id) {
    id = generateDeviceId();
    localStorage.setItem('kr_pwa_device_id', id);
  }
  return id;
}

// --- envia o ping de instalação ---
async function sendInstallPing(reason = 'appinstalled') {
  const payload = { device_uuid: getDeviceId(), reason };
  try {
    const res = await fetch(`${apiBase}/pwa_installed.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
      credentials: 'same-origin',
      cache: 'no-store'
    });

    if (!res.ok) {
      console.warn('sendInstallPing: resposta HTTP não OK', res.status);
      return null;
    }

    let json = null;
    try {
      json = await res.json();
    } catch (err) {
      console.warn('sendInstallPing: erro a fazer parse do JSON', err);
      return null;
    }

    if (json && json.success) {
      // força refresh imediato no contador
      updateInstallCount(true);
    }
    return json;
  } catch (err) {
    console.warn('sendInstallPing error', err);
    return null;
  }
}

// --- formata número em 4k, 2.3M, etc ---
function formatShortNumber(n) {
  if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(1) + 'B';
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
  if (n >= 1_000) return (n / 1_000).toFixed(1) + 'k';
  return String(n);
}

// --- obtém a contagem ao servidor e actualiza o DOM ---
let _lastCount = null;
async function updateInstallCount(force = false) {
  try {
    // cache-bust + força rede
    const url = `${apiBase}/pwa_count.php?t=${Date.now()}`;
    const res = await fetch(url, { cache: 'no-store', credentials: 'same-origin' });

    if (!res.ok) {
      console.warn('updateInstallCount: resposta HTTP não OK', res.status);
      return;
    }

    let json = null;
    try {
      json = await res.json();
    } catch (err) {
      console.warn('updateInstallCount: erro a fazer parse do JSON', err);
      return;
    }

    if (!json || !json.success) return;
    const total = Number(json.total || 0);
    if (!force && total === _lastCount) return;
    _lastCount = total;

    const target =
      document.getElementById('kr_pwa_section_install_count') ||
      document.querySelector('#kr_pwa_section .install-count');
    if (target) {
      target.textContent = formatShortNumber(total);
      target.title = total.toLocaleString();
    }
  } catch (err) {
    console.warn('updateInstallCount error', err);
  }
}

// Hook: quando a app é instalada -> enviar ping
window.addEventListener('appinstalled', () => sendInstallPing('appinstalled'));

// Se a app estiver instalda e for a primeira abertura -> envia first_open
(function sendFirstOpenIfNeeded() {
  try {
    const isStandalone = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches
      || window.navigator.standalone === true;
    if (!isStandalone) return;

    const key = 'kr_pwa_first_open_pinged';
    if (localStorage.getItem(key) === '1') return;

    // tenta enviar, mas não bloqueia a UI
    sendInstallPing('first_open').then(() => {
      localStorage.setItem(key, '1');
    }).catch(() => {
      // nada — tentaremos novamente na próxima carga normal
    });
  } catch (e) {
    // não trava
    console.warn('first_open ping check error', e);
  }
})();

// Auto refresh (polling) — cada 15s
updateInstallCount(true);
setInterval(updateInstallCount, 15000);

// Expor helper para debug/manual trigger
window.kr_updatePwaCount = function (force = true) { return updateInstallCount(force); };