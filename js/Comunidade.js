//--------------------------------------------------------------
// 1. UTILITÁRIOS E EVENTOS DOM INICIAIS
//--------------------------------------------------------------
// Este bloco configura funcionalidades gerais que devem ser ativadas
// assim que o DOM estiver pronto, como: smooth scrolling, efeitos hover,
// carregamento de vídeo e aplicação de classes com base no ranking da galeria.
document.addEventListener('DOMContentLoaded', () => {

  localStorage.setItem('language', 'pt');
  // Adiciona efeitos hover para botões com classe .btn-warning
  const buttons = document.querySelectorAll('.btn-warning');
  buttons.forEach(button => {
    button.addEventListener('mouseenter', () => button.style.transform = 'translateY(-2px)');
    button.addEventListener('mouseleave', () => button.style.transform = 'translateY(0)');
  });

  // Define a opacidade do iframe de vídeo quando ele carregar
  const videoIframe = document.querySelector('.video-iframe');
  if (videoIframe) {
    videoIframe.addEventListener('load', () => {
      videoIframe.style.opacity = '1';
    });
  }
});

//--------------------------------------------------------------
// 4. VÍDEO: ANIMAÇÕES, PARTICULAS, CONTROLE E INTERAÇÃO
//--------------------------------------------------------------
// Este bloco gerencia funcionalidades do vídeo, incluindo:
// - Criação de partículas flutuantes para um efeito visual
// - Animação e loading do vídeo com sobreposição
// - Controle de play/pause e efeitos de hover
// - Efeitos ripple em botões e inserção de keyframes para animação flutuante

document.addEventListener('DOMContentLoaded', function () {
  // Seleciona elementos relacionados ao vídeo e partículas
  const videoIframe = document.querySelector('.video-iframe');
  const loadingOverlay = document.querySelector('.video-loading-overlay');
  const particlesContainer = document.querySelector('.particles-container');

  // Função para criar partículas flutuantes
  function createParticles() {
    for (let i = 0; i < 50; i++) {
      const particle = document.createElement('div');
      particle.className = 'particle';
      particle.style.cssText = `
        position: absolute;
        width: ${Math.random() * 3 + 1}px;
        height: ${Math.random() * 3 + 1}px;
        background: rgba(255, 193, 7, ${Math.random() * 0.5 + 0.2});
        border-radius: 50%;
        left: ${Math.random() * 100}%;
        top: ${Math.random() * 100}%;
        animation: float ${Math.random() * 10 + 5}s linear infinite;
        opacity: ${Math.random() * 0.5 + 0.2};
      `;
      particlesContainer.appendChild(particle);
    }
  }
  // Inicializa as partículas
  createParticles();

  // Gerencia o carregamento do vídeo com animação de fade
  videoIframe.addEventListener('load', function () {
    setTimeout(() => {
      videoIframe.style.opacity = '1';
      loadingOverlay.style.opacity = '0';
      setTimeout(() => {
        loadingOverlay.style.display = 'none';
      }, 300);
    }, 1000);
  });

  // Efeito de hover para o container do vídeo
  const videoContainer = document.querySelector('.video-container');
  videoContainer.addEventListener('mouseenter', function () {
    this.style.transform = 'perspective(1000px) rotateX(0deg) scale(1.02) translateY(-10px)';
  });
  videoContainer.addEventListener('mouseleave', function () {
    this.style.transform = 'perspective(1000px) rotateX(2deg) scale(1) translateY(0)';
  });

  // Adiciona efeito ripple aos botões com a classe .ripple
  document.querySelectorAll('.ripple').forEach(button => {
    button.addEventListener('click', function (e) {
      const ripple = document.createElement('div');
      ripple.className = 'ripple-effect';

      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;

      ripple.style.width = ripple.style.height = `${size}px`;
      ripple.style.left = `${x}px`;
      ripple.style.top = `${y}px`;

      this.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });
  });

  // Adiciona keyframes para animação flutuante das partículas
  const style = document.createElement('style');
  style.textContent = `
    @keyframes float {
      0% { transform: translateY(0) translateX(0); }
      25% { transform: translateY(-20px) translateX(10px); }
      50% { transform: translateY(-40px) translateX(0); }
      75% { transform: translateY(-20px) translateX(-10px); }
      100% { transform: translateY(0) translateX(0); }
    }
  `;
  document.head.appendChild(style);
});