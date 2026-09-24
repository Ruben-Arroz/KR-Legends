/**
 * Inicializa o widget Roblox no elemento #roblox-widget.
 * @param {number} universeId – ID do universo do jogo.
 * @param {object} options – Opções de personalização:
 *   - proxy (boolean): se true, usa AllOrigins para contornar CORS.
 */
async function initRobloxWidget(universeId, options = {}) {
    const container = document.getElementById('roblox-widget');
    const useProxy = !!options.proxy;
    const proxy = url =>
        useProxy
            ? `https://api.allorigins.win/raw?url=${encodeURIComponent(url)}`
            : url;

    // Array de imagens do jogo
    const gameImages = [
        "Imagens/Capas/Capas_sem_logo/capa1_original.jpeg",
        "Imagens/Capas/Capas_logo/Capa 3 - KR Legends.jpg",
        "Imagens/Capas/Capas_logo/Capa 4 - KR Legends.jpg",
        "Imagens/Capas/Capas_logo/Capa 3 - KR Legends.jpg"
    ];

    // Mostrar estado de carregamento
    container.innerHTML = `
    <div class="rbx-card">
      <div class="rbx-loading">
        <div class="rbx-loading-spinner"></div>
        <p>Carregando informações do jogo...</p>
      </div>
    </div>
  `;

    try {
        // 1) Consulta estatísticas, que já inclui name, description, created & updated
        const url = proxy(
            `https://games.roblox.com/v1/games?universeIds=${universeId}`
        );
        const res = await fetch(url, { mode: 'cors' });

        if (!res.ok) {
            throw new Error(`Falha ao carregar dados (status: ${res.status})`);
        }

        const data = (await res.json()).data[0];

        // Extrai todos os campos necessários (incluindo created)
        const {
            name: title = 'KR Legends',
            description: desc = 'Um jogo de corridas baseado na tua paixão por carros.',
            playing = 0,
            visits = 0,
            created,
            updated
        } = data;

        // 2) Likes / Dislikes
        const votesRes = await fetch(
            proxy(`https://games.roblox.com/v1/games/votes?universeIds=${universeId}`),
            { mode: 'cors' }
        );

        if (!votesRes.ok) {
            throw new Error(`Falha ao carregar votos (status: ${votesRes.status})`);
        }

        const votes = (await votesRes.json()).data[0];
        const { upVotes = 0, downVotes = 0 } = votes || {};

        // 3) Favoritos
        const favRes = await fetch(
            proxy(`https://games.roblox.com/v1/games/${universeId}/favorites/count`),
            { mode: 'cors' }
        );

        if (!favRes.ok) {
            throw new Error(`Falha ao carregar favoritos (status: ${favRes.status})`);
        }

        const favData = await favRes.json();
        const favorites = favData.favoritesCount ?? 0;

        // Formatação de números para melhor visualização
        const formatNumber = (num) => {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            } else if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        };

        // Formatar datas
        const formatDate = (dateString) => {
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        };

        // Gerar HTML para as miniaturas
        const thumbnailsHTML = gameImages.map((img, index) => `
            <img src="${img}" 
                 alt="KR Legends Screenshot ${index + 1}" 
                 class="rbx-thumbnail ${index === 0 ? 'active' : ''}"
                 onclick="changeMainImage(this.src)"
            >
        `).join('');

        // 4) Renderização
        container.innerHTML = `
      <div class="rbx-card">
        <div class="rbx-content">
          <div class="rbx-image-container">
            <div class="rbx-thumbnail-container">
              ${thumbnailsHTML}
            </div>
          </div>
          
          <div class="rbx-info">
            <div class="rbx-header">
              <h2 class="rbx-title">${title}</h2>
              <div class="rbx-desc">${desc}</div>
            </div>
            
            <div class="rbx-stats-container">
              <ul class="rbx-stats">
                <li class="rbx-stat-item">
                  <i class="bi bi-people-fill rbx-stat-icon"></i>
                  <span class="rbx-stat-label">Online</span>
                  <span class="rbx-stat-value">${formatNumber(playing)}</span>
                </li>
                <li class="rbx-stat-item">
                  <i class="bi bi-eye-fill rbx-stat-icon"></i>
                  <span class="rbx-stat-label">Visitas</span>
                  <span class="rbx-stat-value">${formatNumber(visits)}</span>
                </li>
                <li class="rbx-stat-item">
                  <i class="bi bi-calendar-check rbx-stat-icon"></i>
                  <span class="rbx-stat-label">Criado em</span>
                  <span class="rbx-stat-value">${formatDate(created)}</span>
                </li>
                <li class="rbx-stat-item">
                  <i class="bi bi-calendar-date rbx-stat-icon"></i>
                  <span class="rbx-stat-label">Atualizado</span>
                  <span class="rbx-stat-value">${formatDate(updated)}</span>
                </li>
              </ul>
              
              <ul class="rbx-stats">
                <li class="rbx-stat-item">
                  <i class="bi bi-hand-thumbs-up-fill rbx-stat-icon"></i>
                  <span class="rbx-stat-label">Likes</span>
                  <span class="rbx-stat-value">${formatNumber(upVotes)}</span>
                </li>
                <li class="rbx-stat-item">
                  <i class="bi bi-hand-thumbs-down-fill rbx-stat-icon"></i>
                  <span class="rbx-stat-label">Dislikes</span>
                  <span class="rbx-stat-value">${formatNumber(downVotes)}</span>
                </li>
                <li class="rbx-stat-item">
                  <i class="bi bi-percent rbx-stat-icon"></i>
                  <span class="rbx-stat-label">Aprovação</span>
                  <span class="rbx-stat-value">${Math.round((upVotes / (upVotes + downVotes || 1)) * 100)}%</span>
                </li>
                <li class="rbx-stat-item">
                  <i class="bi bi-star-fill rbx-stat-icon"></i>
                  <span class="rbx-stat-label">Favoritos</span>
                  <span class="rbx-stat-value">${formatNumber(favorites)}</span>
                </li>
              </ul>
            </div>
            
            <div class="rbx-play-button">
              <a href="https://www.roblox.com/pt/games/113586179382037/KR-Legends-Beta" target="_blank" class="rbx-play-btn">
                <i class="bi bi-controller"></i>
                Jogar Agora
              </a>
            </div>
          </div>
        </div>
      </div>
    `;

        // Adicionar função para trocar imagens ao escopo global
        window.changeMainImage = function(src) {
            const mainImage = container.querySelector('.rbx-main-image');
            const thumbnails = container.querySelectorAll('.rbx-thumbnail');
            
            mainImage.src = src;
            thumbnails.forEach(thumb => {
                thumb.classList.toggle('active', thumb.src === src);
            });
        };

    } catch (error) {
        container.innerHTML = `
      <div class="rbx-card">
        <div class="rbx-error">
          <i class="bi bi-exclamation-triangle-fill" style="font-size: 2rem; margin-bottom: 1rem;"></i>
          <h3>Não foi possível carregar os dados do jogo</h3>
          <p>${error.message || 'Ocorreu um erro ao tentar carregar as informações. Tente novamente mais tarde.'}</p>
        </div>
      </div>
    `;
        console.error('Roblox Widget Error:', error);
    }
}