// cookieConsent.js
(() => {
  const KEY = 'kr_cookies_accepted';

  // Estado interno
  const prefs = {
    necessary: true,
    analytics: false,
    functional: false,
    marketing: false
  };

  // Persistência
  function savePrefs() {
    localStorage.setItem(KEY, JSON.stringify(prefs));
  }
  function loadPrefs() {
    const stored = localStorage.getItem(KEY);
    if (stored) {
      Object.assign(prefs, JSON.parse(stored));
      return true;
    }
    return false;
  }

  // Hooks para ligar scripts conforme categorias
  function applyPrefs() {
    console.log('🍪 Aplicar preferências:', prefs);
    // ex: if(prefs.analytics) loadGoogleAnalytics();
  }

  // Cria e injeta o banner + modal no body
  function buildBannerAndModal() {
    const html = `
      <!-- Banner -->
      <div class="cookie-consent-banner" id="cookieConsentBanner">
        <div class="cookie-banner-content">
          <div class="cookie-banner-icon">
            <i class="fas fa-cookie-bite"></i>
          </div>
          <div class="cookie-banner-text">
            <h3>Este site utiliza cookies</h3>
            <p>
              Utilizamos cookies para melhorar a sua experiência no nosso site. 
              Ao continuar a navegar, concorda com a nossa 
              <a href="Politica.php" class="cookie-policy-link" target="_blank">Política de Cookies</a>.
            </p>
          </div>
          <div class="cookie-banner-buttons">
            <button class="btn cookie-btn cookie-btn-accept-all">Aceitar Todos</button>
            <button class="btn cookie-btn cookie-btn-settings">Personalizar</button>
            <button class="btn cookie-btn cookie-btn-accept-necessary">Apenas Necessários</button>
          </div>
        </div>
      </div>
      <!-- Modal -->
      <div class="cookie-settings-modal" id="cookieSettingsModal">
        <div class="cookie-modal-content">
          <div class="cookie-modal-header">
            <h3>Preferências de Cookies</h3>
            <button class="cookie-modal-close"><i class="fas fa-times"></i></button>
          </div>
          <div class="cookie-modal-body">
            <p>
              Selecione quais os tipos de cookies que pretende aceitar. 
              Os cookies estritamente necessários são essenciais para o funcionamento do site.
            </p>
            <div class="cookie-preference-item">
              <div class="cookie-preference-header">
                <div class="cookie-preference-title">
                  <h4>Cookies Estritamente Necessários</h4>
                  <p>Essenciais para o funcionamento do site</p>
                </div>
                <div class="cookie-toggle disabled">
                  <input type="checkbox" id="necessary-cookies" checked disabled>
                  <label for="necessary-cookies" class="toggle-switch"></label>
                </div>
              </div>
            </div>
            <div class="cookie-preference-item">
              <div class="cookie-preference-header">
                <div class="cookie-preference-title">
                  <h4>Cookies de Desempenho e Estatística</h4>
                  <p>Para análise de tráfego e melhorias no site</p>
                </div>
                <div class="cookie-toggle">
                  <input type="checkbox" id="analytics-cookies">
                  <label for="analytics-cookies" class="toggle-switch"></label>
                </div>
              </div>
            </div>
            <div class="cookie-preference-item">
              <div class="cookie-preference-header">
                <div class="cookie-preference-title">
                  <h4>Cookies de Funcionalidade</h4>
                  <p>Para guardar preferências e personalizar sua experiência</p>
                </div>
                <div class="cookie-toggle">
                  <input type="checkbox" id="functional-cookies">
                  <label for="functional-cookies" class="toggle-switch"></label>
                </div>
              </div>
            </div>
            <div class="cookie-preference-item">
              <div class="cookie-preference-header">
                <div class="cookie-preference-title">
                  <h4>Cookies de Marketing e Publicidade</h4>
                  <p>Para publicidade personalizada</p>
                </div>
                <div class="cookie-toggle">
                  <input type="checkbox" id="marketing-cookies">
                  <label for="marketing-cookies" class="toggle-switch"></label>
                </div>
              </div>
            </div>
          </div>
          <div class="cookie-modal-footer">
            <button class="btn cookie-btn-save">Guardar Preferências</button>
            <button class="btn cookie-btn-accept-all-modal">Aceitar Todos</button>
            <button class="btn cookie-btn-reject-all">Rejeitar Não Essenciais</button>
          </div>
        </div>
      </div>
    `;
    // Injeta
    document.body.insertAdjacentHTML('beforeend', html);

    // Seletores
    const BANNER = document.getElementById('cookieConsentBanner');
    const MODAL = document.getElementById('cookieSettingsModal');
    const btnAall = BANNER.querySelector('.cookie-btn-accept-all');
    const btnSett = BANNER.querySelector('.cookie-btn-settings');
    const btnNec = BANNER.querySelector('.cookie-btn-accept-necessary');
    const btnClose = MODAL.querySelector('.cookie-modal-close');
    const btnSave = MODAL.querySelector('.cookie-btn-save');
    const btnMall = MODAL.querySelector('.cookie-btn-accept-all-modal');
    const btnRej = MODAL.querySelector('.cookie-btn-reject-all');
    const chkAn = MODAL.querySelector('#analytics-cookies');
    const chkFn = MODAL.querySelector('#functional-cookies');
    const chkMk = MODAL.querySelector('#marketing-cookies');

    // Mostrar/esconder
    const showB = () => BANNER.classList.add('visible');
    const hideB = () => BANNER.classList.remove('visible');
    const showM = () => MODAL.classList.add('visible');
    const hideM = () => MODAL.classList.remove('visible');

    // Ligações
    btnAall.addEventListener('click', () => {
      Object.keys(prefs).forEach(k => prefs[k] = true);
      savePrefs(); applyPrefs(); hideB();
    });
    btnNec.addEventListener('click', () => {
      Object.keys(prefs).forEach(k => prefs[k] = false);
      prefs.necessary = true;
      savePrefs(); applyPrefs(); hideB();
    });
    btnSett.addEventListener('click', () => {
      hideB();
      chkAn.checked = prefs.analytics;
      chkFn.checked = prefs.functional;
      chkMk.checked = prefs.marketing;
      showM();
    });
    btnClose.addEventListener('click', () => { hideM(); showB(); });
    btnSave.addEventListener('click', () => {
      prefs.analytics = chkAn.checked;
      prefs.functional = chkFn.checked;
      prefs.marketing = chkMk.checked;
      savePrefs(); applyPrefs(); hideM();
    });
    btnMall.addEventListener('click', () => {
      Object.keys(prefs).forEach(k => prefs[k] = true);
      savePrefs(); applyPrefs(); hideM();
    });
    btnRej.addEventListener('click', () => {
      Object.keys(prefs).forEach(k => prefs[k] = false);
      prefs.necessary = true;
      savePrefs(); applyPrefs(); hideM();
    });

    // Aplica animação de slide
    requestAnimationFrame(showB);
  }

  // Inicialização
  document.addEventListener('DOMContentLoaded', () => {
    if (!loadPrefs()) {
      buildBannerAndModal();
    } else {
      applyPrefs();
    }
  });
})();