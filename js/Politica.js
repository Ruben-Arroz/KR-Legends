//import javascriptLogo from './javascript.svg';
//import viteLogo from '/vite.svg';
//import { setupCounter } from './counter.js';

// Initialize the main application
document.addEventListener('DOMContentLoaded', () => {
  // Setup counter from the counter.js file
  const counterElement = document.querySelector('#counter');
  if (counterElement) {
    setupCounter(counterElement);
  }

  // Initialize accordion functionality
  initAccordion();

  // Initialize cookie consent banner
  initCookieConsent();

  // Add scroll animation effect
  initScrollAnimation();
  
  // Initialize version history modal
  initVersionHistoryModal();
});

// Accordion functionality
function initAccordion() {
  const accordionItems = document.querySelectorAll('.accordion-item');

  accordionItems.forEach(item => {
    const header = item.querySelector('.accordion-header');

    header.addEventListener('click', () => {
      const isActive = item.classList.contains('active');

      // Close all accordion items
      accordionItems.forEach(accordion => {
        accordion.classList.remove('active');
      });

      // Toggle the clicked item
      if (!isActive) {
        item.classList.add('active');
      }
    });
  });

  // Open the first accordion item by default
  if (accordionItems.length > 0) {
    accordionItems[0].classList.add('active');
  }
}

// Cookie consent functionality
function initCookieConsent() {
  const cookieConsentBanner = document.getElementById('cookieConsentBanner');
  const cookieSettingsModal = document.getElementById('cookieSettingsModal');
  const cookieSettingsBtn = document.querySelector('.cookie-settings-btn');
  const cookieBannerSettingsBtn = document.querySelector('.cookie-btn-settings');
  const acceptAllBtn = document.querySelector('.cookie-btn-accept-all');
  const acceptNecessaryBtn = document.querySelector('.cookie-btn-accept-necessary');
  const modalCloseBtn = document.querySelector('.cookie-modal-close');
  const savePreferencesBtn = document.querySelector('.cookie-btn-save');
  const acceptAllModalBtn = document.querySelector('.cookie-btn-accept-all-modal');
  const rejectAllBtn = document.querySelector('.cookie-btn-reject-all');

  // Check if cookies are already accepted
  const cookiesAccepted = localStorage.getItem('cookiesAccepted');

  if (!cookiesAccepted) {
    // Show the cookie banner with a delay
    setTimeout(() => {
      cookieConsentBanner.classList.add('visible');
    }, 1000);
  }

  // Accept all cookies
  function acceptAllCookies() {
    localStorage.setItem('cookiesAccepted', 'all');
    localStorage.setItem('analyticsCookies', 'true');
    localStorage.setItem('functionalCookies', 'true');
    localStorage.setItem('marketingCookies', 'true');
    cookieConsentBanner.classList.remove('visible');
    cookieSettingsModal.classList.remove('visible');
    updateToggleStates();
  }

  // Accept only necessary cookies
  function acceptNecessaryCookies() {
    localStorage.setItem('cookiesAccepted', 'necessary');
    localStorage.setItem('analyticsCookies', 'false');
    localStorage.setItem('functionalCookies', 'false');
    localStorage.setItem('marketingCookies', 'false');
    cookieConsentBanner.classList.remove('visible');
    cookieSettingsModal.classList.remove('visible');
    updateToggleStates();
  }

  // Open settings modal
  function openSettingsModal() {
    updateToggleStates();
    cookieSettingsModal.classList.add('visible');
  }

  // Close settings modal
  function closeSettingsModal() {
    cookieSettingsModal.classList.remove('visible');
  }

  // Save preferences
  function savePreferences() {
    const analyticsCookies = document.getElementById('analytics-cookies').checked;
    const functionalCookies = document.getElementById('functional-cookies').checked;
    const marketingCookies = document.getElementById('marketing-cookies').checked;

    localStorage.setItem('cookiesAccepted', 'custom');
    localStorage.setItem('analyticsCookies', analyticsCookies);
    localStorage.setItem('functionalCookies', functionalCookies);
    localStorage.setItem('marketingCookies', marketingCookies);

    cookieConsentBanner.classList.remove('visible');
    cookieSettingsModal.classList.remove('visible');
  }

  // Update toggle states based on saved preferences
  function updateToggleStates() {
    const analyticsCookies = localStorage.getItem('analyticsCookies') === 'true';
    const functionalCookies = localStorage.getItem('functionalCookies') === 'true';
    const marketingCookies = localStorage.getItem('marketingCookies') === 'true';

    document.getElementById('analytics-cookies').checked = analyticsCookies;
    document.getElementById('functional-cookies').checked = functionalCookies;
    document.getElementById('marketing-cookies').checked = marketingCookies;
  }

  // Event listeners
  if (acceptAllBtn) acceptAllBtn.addEventListener('click', acceptAllCookies);
  if (acceptNecessaryBtn) acceptNecessaryBtn.addEventListener('click', acceptNecessaryCookies);
  if (cookieBannerSettingsBtn) cookieBannerSettingsBtn.addEventListener('click', openSettingsModal);
  if (cookieSettingsBtn) cookieSettingsBtn.addEventListener('click', openSettingsModal);
  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeSettingsModal);
  if (savePreferencesBtn) savePreferencesBtn.addEventListener('click', savePreferences);
  if (acceptAllModalBtn) acceptAllModalBtn.addEventListener('click', acceptAllCookies);
  if (rejectAllBtn) rejectAllBtn.addEventListener('click', acceptNecessaryCookies);

  // Close modal when clicking outside
  cookieSettingsModal.addEventListener('click', (e) => {
    if (e.target === cookieSettingsModal) {
      closeSettingsModal();
    }
  });
}

// Scroll animation
function initScrollAnimation() {
  const fadeElements = document.querySelectorAll('.fade-in');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        // Add a "visible" class for more complex animations
        entry.target.classList.add('visible');
        // Stop observing once it's visible
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
  });

  fadeElements.forEach(element => {
    element.style.opacity = '0';
    element.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
    observer.observe(element);
  });
}

// Version history modal functionality
function initVersionHistoryModal() {
  const versionHistoryBtn = document.querySelector('.version-history-btn');
  
  if (versionHistoryBtn) {
    // Create the modal element if it doesn't already exist
    let versionHistoryModal = document.getElementById('versionHistoryModal');
    
    if (!versionHistoryModal) {
      versionHistoryModal = document.createElement('div');
      versionHistoryModal.classList.add('modal-version-history');
      versionHistoryModal.setAttribute('id', 'versionHistoryModal');
      document.body.appendChild(versionHistoryModal);
    }
    
    // Create a loading state for the modal
    versionHistoryModal.innerHTML = `
      <div class="modal-content-version">
        <div class="modal-header-version">
          <h4 class="modal-title-version">Histórico de Versões</h4>
          <button class="modal-close-btn-version">&times;</button>
        </div>
        <div class="modal-body-version">
          <p>Carregando histórico...</p>
        </div>
        <div class="modal-footer-version">
          <button class="modal-close-btn-version">Fechar</button>
        </div>
      </div>
    `;
    
    // Add click event to the button IMMEDIATELY (don't wait for fetch)
    versionHistoryBtn.addEventListener('click', () => {
      // Show the modal
      versionHistoryModal.classList.add('visible');
      
      // Fetch the data only if we haven't already done so
      if (!versionHistoryModal.dataset.loaded) {
        loadVersionHistory(versionHistoryModal);
      }
    });
    
    // Setup close functionality
    setupModalClose(versionHistoryModal);
  }
}

// Load version history data
function loadVersionHistory(modal) {
  const modalBody = modal.querySelector('.modal-body-version');
  
  fetch('JSON/Infos/policy_versionHistory.json')
    .then(response => {
      if (!response.ok) {
        throw new Error(`Erro HTTP: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      // Mark the modal as loaded
      modal.dataset.loaded = 'true';
      
      // Update the modal content with the version history
      if (data && data.versionHistory && data.versionHistory.length > 0) {
        modalBody.innerHTML = `
          <ul class="version-list-modal">
            ${data.versionHistory.map(item => `
              <li><strong>${item.date}</strong>: ${item.version}</li>
            `).join('')}
          </ul>
        `;
      } else {
        modalBody.innerHTML = '<p>Nenhum histórico de versão encontrado.</p>';
      }
    })
    .catch(error => {
      console.error('Erro ao carregar o histórico de versões:', error);
      modalBody.innerHTML = `<p>Erro ao carregar o histórico de versões: ${error.message}</p>`;
    });
}

// Setup modal close functionality
function setupModalClose(modal) {
  // Handle close button clicks
  modal.addEventListener('click', (e) => {
    if (
      e.target.classList.contains('modal-close-btn-version') || 
      e.target === modal
    ) {
      modal.classList.remove('visible');
    }
  });
  
  // Handle ESC key press
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('visible')) {
      modal.classList.remove('visible');
    }
  });
}

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', () => {
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const navLinks = document.querySelector('.nav-links');

  if (mobileMenuBtn && navLinks) {
    mobileMenuBtn.addEventListener('click', () => {
      navLinks.classList.toggle('active');
      mobileMenuBtn.classList.toggle('active');

      if (navLinks.classList.contains('active')) {
        navLinks.style.display = 'flex';
        navLinks.style.flexDirection = 'column';
        navLinks.style.position = 'absolute';
        navLinks.style.top = '100%';
        navLinks.style.left = '0';
        navLinks.style.right = '0';
        navLinks.style.backgroundColor = 'var(--color-dark-gray)';
        navLinks.style.padding = 'var(--spacing-md)';
        navLinks.style.zIndex = '90';
        navLinks.style.boxShadow = 'var(--shadow-md)';
      } else {
        navLinks.style.display = '';
      }
    });
  }
});