// js/modos-de-jogo.js
/**
 * KR Legends - Game Modes Page Script
 * Handles dynamic content loading and interactions for the game modes page
 */

document.addEventListener('DOMContentLoaded', function () {
    loadGameModes();
    initAnimationObservers();
    setupVideoPlayers();
});

async function loadGameModes() {
    try {
        const response = await fetch('JSON/Infos/modos-de-jogo.json');
        const { gameModes } = await response.json();

        // Render available game modes
        const available = gameModes.filter(m => m.status === 'available');
        renderGameModes(available, 'available-modes-container');

        // Render future game modes
        const future = gameModes.filter(m => m.status !== 'available');
        renderGameModes(future, 'future-modes-container');
    } catch (error) {
        console.error('Error loading game modes:', error);
        document.getElementById('available-modes-container').innerHTML =
            '<p class="text-danger">Erro ao carregar modos de jogo. Por favor, tente novamente mais tarde.</p>';
        document.getElementById('future-modes-container').innerHTML =
            '<p class="text-danger">Erro ao carregar modos de jogo futuros. Por favor, tente novamente mais tarde.</p>';
    }
}

function renderGameModes(modes, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    const row = document.createElement('div');
    row.className = 'row';
    modes.forEach(mode => {
        row.appendChild(createGameModeElement(mode));
    });
    container.appendChild(row);
}

function createGameModeElement(mode) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 mb-4 fade-up';

    let statusClass = '';
    switch (mode.status) {
        case 'available': statusClass = 'status-available'; break;
        case 'coming-soon': statusClass = 'status-coming-soon'; break;
        case 'planning': statusClass = 'status-planning'; break;
        case 'secret': statusClass = 'status-secret'; break;
        default: statusClass = 'status-planning';
    }

    const isLocked = mode.status !== 'available';

    col.innerHTML = `
    <div class="game-mode-card ${isLocked ? 'game-mode-locked' : ''}">
      <div class="game-mode-media">
        ${mode.media.type === 'video'
            ? `<img src="${mode.media.thumbnail}" alt="${mode.title}" data-video="${mode.media.src}" class="video-thumbnail" />`
            : `<img src="${mode.media.src}" alt="${mode.title}" />`
        }
        ${mode.media.type === 'video'
            ? `<div class="play-button" data-video="${mode.media.src}" role="button" tabindex="0"></div>`
            : ``
        }
        ${isLocked
            ? `<div class="lock-overlay">
              <i class="bi bi-lock-fill lock-icon"></i>
              <p class="lock-text">${mode.statusText}</p>
            </div>`
            : ``
        }
      </div>
      <div class="game-mode-content">
        <span class="mode-status ${statusClass}">${mode.statusText}</span>
        <h3 class="game-mode-title">${mode.title}</h3>
        <p class="game-mode-description">${mode.description}</p>
        <ul class="game-mode-features">
          ${mode.features.map(f => `<li>${f}</li>`).join('')}
        </ul>
        ${mode.status === 'available'
            ? `<a href="https://www.roblox.com/pt/games/113586179382037/KR-Legends-Beta" target="_blank" class="mode-button">
              <i class="bi bi-controller"></i> Jogar Agora
             </a>`
            : ``
        }
      </div>
    </div>
    `;
    return col;
}

function initAnimationObservers() {
    const fadeObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                fadeObserver.unobserve(entry.target);
            }
        });
    }, { root: null, threshold: 0.2, rootMargin: '-50px' });

    setTimeout(() => {
        document.querySelectorAll('.fade-up').forEach(el => fadeObserver.observe(el));
    }, 100);
}

function setupVideoPlayers() {
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('play-button') ||
            event.target.classList.contains('video-thumbnail') ||
            event.target.closest('.play-button')) {

            const btn = event.target.closest('[data-video]');
            const videoSrc = btn.dataset.video;
            const mediaContainer = btn.closest('.game-mode-media');
            if (mediaContainer.querySelector('iframe')) return;

            const iframe = document.createElement('iframe');
            iframe.src = `${videoSrc}?autoplay=1&muted=0`;
            iframe.frameBorder = '0';
            iframe.allow = 'autoplay; fullscreen';
            Object.assign(iframe.style, {
                position: 'absolute', top: '0', left: '0',
                width: '100%', height: '100%'
            });

            mediaContainer.querySelector('img').remove();
            mediaContainer.querySelector('.play-button').remove();
            mediaContainer.appendChild(iframe);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && document.activeElement.classList.contains('play-button')) {
            document.activeElement.click();
        }
    });
}