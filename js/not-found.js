/**
 * KR LEGENDS - 404 NOT FOUND PAGE SCRIPTS
 * Scripts melhorados com foco na qualidade e responsividade
 * Versão otimizada com menos poluição visual
 */

(function () {
    'use strict';

    // Configurações globais simplificadas
    const NOTFND_CONFIG = {
        ANIMATION_DURATION: {
            FAST: 200,
            NORMAL: 300,
            SLOW: 500
        },
        SECURITY: {
            MAX_INPUT_LENGTH: 100,
            ALLOWED_PROTOCOLS: ['http:', 'https:'],
            XSS_PATTERNS: [/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, /javascript:/gi, /on\w+=/gi]
        },
        ANALYTICS: {
            INTERACTION_THROTTLE: 500,
            SESSION_TIMEOUT: 30000
        }
    };

    // Sanitização de entrada
    const sanitizeInput = (input) => {
        if (typeof input !== 'string') return '';

        let sanitized = input
            .replace(/[<>]/g, '')
            .replace(/javascript:/gi, '')
            .replace(/on\w+=/gi, '')
            .replace(/data:/gi, '')
            .trim()
            .substring(0, NOTFND_CONFIG.SECURITY.MAX_INPUT_LENGTH);

        NOTFND_CONFIG.SECURITY.XSS_PATTERNS.forEach(pattern => {
            sanitized = sanitized.replace(pattern, '');
        });

        return sanitized;
    };

    // Sanitização de URLs
    const sanitizeURL = (url) => {
        try {
            const urlObj = new URL(url, window.location.origin);
            if (!NOTFND_CONFIG.SECURITY.ALLOWED_PROTOCOLS.includes(urlObj.protocol)) {
                return window.location.origin;
            }
            return urlObj.href;
        } catch (e) {
            return window.location.origin;
        }
    };

    // Classe principal da página 404
    class NotFoundPageManager {
        constructor() {
            this.init();
            this.bindEvents();
            this.startAnimations();
            this.initializeAnalytics();
            this.setupResponsiveOptimizations();
        }

        init() {
            // Estado da aplicação
            this.state = {
                glitchInterval: null,
                sparkleInterval: null,
                userStartTime: Date.now(),
                interactionCount: 0,
                lastInteraction: 0,
                isVisible: true,
                animationsEnabled: !window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                isMobile: window.innerWidth < 768
            };

            // Cache de elementos DOM
            this.elements = {
                errorCode: document.querySelector('.notfnd-error-code'),
                actionButtons: document.querySelectorAll('.notfnd-btn'),
                helpLinks: document.querySelectorAll('.notfnd-help-link'),
                statusIndicator: document.querySelector('.notfnd-status-indicator'),
                searchModal: document.getElementById('notfnd-search-modal'),
                searchInput: document.getElementById('notfnd-search-input'),
                searchForm: document.getElementById('notfnd-search-form'),
                container: document.querySelector('.notfnd-container'),
                floatingElements: document.querySelector('.notfnd-floating-elements'),
                racingLines: document.querySelector('.notfnd-racing-lines'),
                particles: document.querySelector('.notfnd-particles')
            };

            // Verificar suporte a recursos
            this.features = {
                intersectionObserver: 'IntersectionObserver' in window,
                performanceObserver: 'PerformanceObserver' in window
            };

            console.log('🏁 KR Legends 404 Page Manager initialized');
        }

        bindEvents() {
            // Botões de ação
            this.elements.actionButtons.forEach(button => {
                button.addEventListener('click', (e) => this.handleActionClick(e));
                button.addEventListener('mouseenter', () => this.handleButtonHover());
            });

            // Links de ajuda
            this.elements.helpLinks.forEach(link => {
                link.addEventListener('click', (e) => this.handleHelpClick(e));
            });

            // Eventos de teclado
            document.addEventListener('keydown', (e) => this.handleKeyboard(e));

            // Eventos de navegação
            window.addEventListener('popstate', () => this.handleBackNavigation());
            window.addEventListener('beforeunload', () => this.handlePageUnload());

            // Eventos de visibilidade
            document.addEventListener('visibilitychange', () => this.handleVisibilityChange());

            // Eventos de formulário de pesquisa
            if (this.elements.searchForm) {
                this.elements.searchForm.addEventListener('submit', (e) => this.handleSearchSubmit(e));
            }

            // Eventos de modal
            document.addEventListener('click', (e) => this.handleModalClick(e));

            // Eventos de redimensionamento
            window.addEventListener('resize', this.debounce(() => this.handleResize(), 250));

            // Eventos de scroll
            window.addEventListener('scroll', this.throttle(() => this.handleScroll(), 100));

            // Configurar observador de interseção se disponível
            if (this.features.intersectionObserver) {
                this.setupIntersectionObserver();
            }
        }

        // Manipuladores de eventos
        handleActionClick(event) {
            const button = event.currentTarget;
            const action = button.textContent.trim();
            const now = Date.now();

            // Throttling de interações
            if (now - this.state.lastInteraction < NOTFND_CONFIG.ANALYTICS.INTERACTION_THROTTLE) {
                return;
            }

            this.state.interactionCount++;
            this.state.lastInteraction = now;
            this.logInteraction('button_click', { action, timestamp: now });

            // Feedback visual
            this.addClickFeedback(button);

            // Ações específicas
            if (action.toLowerCase().includes('início') || action.toLowerCase().includes('home')) {
                this.navigateToHome();
            } else if (action.toLowerCase().includes('anterior') || action.toLowerCase().includes('back')) {
                this.navigateBack();
            } else if (action.toLowerCase().includes('procurar') || action.toLowerCase().includes('search')) {
                this.openSearchModal();
            }
        }

        handleHelpClick(event) {
            const link = event.currentTarget;
            const helpType = link.querySelector('.notfnd-help-link-title')?.textContent.trim() || 'unknown';

            this.logInteraction('help_click', { helpType, url: link.href });
            this.addClickFeedback(link);

            // Adicionar delay para feedback visual
            setTimeout(() => {
                if (link.href && link.href !== '#') {
                    window.location.href = link.href;
                }
            }, 150);

            event.preventDefault();
        }

        handleKeyboard(event) {
            // Atalhos de teclado
            if (event.ctrlKey || event.metaKey) {
                switch (event.key.toLowerCase()) {
                    case 'h':
                        event.preventDefault();
                        this.navigateToHome();
                        break;
                    case 'b':
                        event.preventDefault();
                        this.navigateBack();
                        break;
                    case 'f':
                    case 'k':
                        event.preventDefault();
                        this.openSearchModal();
                        break;
                }
            }

            // ESC para fechar modal
            if (event.key === 'Escape') {
                if (this.elements.searchModal && this.elements.searchModal.style.display === 'flex') {
                    this.closeSearchModal();
                } else {
                    this.navigateBack();
                }
            }

            // Enter para ações rápidas
            if (event.key === 'Enter' && event.target.classList.contains('notfnd-search-tag')) {
                const searchTerm = event.target.textContent.trim();
                this.performSearch(searchTerm);
            }
        }

        handleSearchSubmit(event) {
            event.preventDefault();
            const searchTerm = this.elements.searchInput?.value.trim();

            if (searchTerm) {
                this.performSearch(searchTerm);
            }
        }

        handleModalClick(event) {
            // Fechar modal ao clicar fora
            if (event.target === this.elements.searchModal) {
                this.closeSearchModal();
            }

            // Manipular cliques em tags de pesquisa
            if (event.target.classList.contains('notfnd-search-tag')) {
                const searchTerm = event.target.textContent.trim();
                this.performSearch(searchTerm);
            }
        }

        handleVisibilityChange() {
            this.state.isVisible = !document.hidden;

            if (this.state.isVisible) {
                this.resumeAnimations();
            } else {
                this.pauseAnimations();
            }
        }

        handleResize() {
            const wasMobile = this.state.isMobile;
            this.state.isMobile = window.innerWidth < 768;

            // Otimizar para mudança de dispositivo
            if (wasMobile !== this.state.isMobile) {
                this.optimizeForDevice();
            }

            this.logInteraction('window_resize', {
                width: window.innerWidth,
                height: window.innerHeight,
                isMobile: this.state.isMobile
            });
        }

        handleScroll() {
            // Parallax sutil
            const scrollY = window.scrollY;

            if (this.elements.floatingElements && scrollY > 0) {
                const parallaxOffset = scrollY * 0.05;
                this.elements.floatingElements.style.transform = `translateY(${parallaxOffset}px)`;
            }
        }

        handleButtonHover() {
            // Feedback sonoro simples se suportado
            this.playHoverFeedback();
        }

        handleBackNavigation() {
            this.logInteraction('back_navigation', {
                type: 'browser_back',
                timestamp: Date.now()
            });
        }

        handlePageUnload() {
            this.logInteraction('page_unload', {
                timeSpent: Date.now() - this.state.userStartTime,
                interactions: this.state.interactionCount,
                timestamp: Date.now()
            });
        }

        // Navegação e ações
        navigateToHome() {
            this.showLoadingState('Redirecionando para o início...');

            const homeUrls = [
                '/~a29621/KRLegends/Index.php',
                '/~a29621/KRLegends/index.php',
                '/~a29621/KRLegends/',
                '/'
            ];

            this.tryNavigateToUrls(homeUrls, 'home');
        }

        navigateBack() {
            this.showLoadingState('Voltando...');

            if (window.history.length > 1) {
                setTimeout(() => {
                    window.history.back();
                }, 300);
            } else {
                this.navigateToHome();
            }
        }

        openSearchModal() {
            if (this.elements.searchModal) {
                this.elements.searchModal.style.display = 'flex';

                // Focar no input de pesquisa
                setTimeout(() => {
                    if (this.elements.searchInput) {
                        this.elements.searchInput.focus();
                    }
                }, 100);

                this.logInteraction('search_modal_open', { timestamp: Date.now() });
            }
        }

        closeSearchModal() {
            if (this.elements.searchModal) {
                this.elements.searchModal.style.display = 'none';
                this.logInteraction('search_modal_close', { timestamp: Date.now() });
            }
        }

        performSearch(searchTerm) {
            const sanitizedTerm = sanitizeInput(searchTerm);

            if (!sanitizedTerm) {
                this.showErrorMessage('Termo de pesquisa inválido.');
                return;
            }

            this.logInteraction('search_performed', { term: sanitizedTerm });
            this.showLoadingState(`Procurando por "${sanitizedTerm}"...`);

            const searchUrls = [
                `/~a29621/KRLegends/search.php?q=${encodeURIComponent(sanitizedTerm)}`,
                `/~a29621/KRLegends/Index.php?search=${encodeURIComponent(sanitizedTerm)}`,
                `/~a29621/KRLegends/?s=${encodeURIComponent(sanitizedTerm)}`
            ];

            this.tryNavigateToUrls(searchUrls, 'search');
            this.closeSearchModal();
        }

        tryNavigateToUrls(urls, type) {
            let currentIndex = 0;

            const tryNext = () => {
                if (currentIndex >= urls.length) {
                    const errorMessage = type === 'home'
                        ? 'Não foi possível voltar ao início. Verifique sua conexão.'
                        : 'Não foi possível realizar a pesquisa. Tente novamente.';

                    this.showErrorMessage(errorMessage);
                    this.hideLoadingState();
                    return;
                }

                const url = urls[currentIndex];

                // Tentar navegar diretamente
                setTimeout(() => {
                    window.location.href = sanitizeURL(url);
                }, 300);

                currentIndex++;
            };

            tryNext();
        }

        // Animações simplificadas
        startAnimations() {
            if (!this.state.animationsEnabled) return;

            this.startGlitchEffect();
            this.startSparkleEffect();
            this.startTypingEffect();
        }

        startGlitchEffect() {
            if (!this.elements.errorCode) return;

            const glitchChars = ['4', '0', '4', '█', '▓', '▒'];
            const originalText = '404';

            this.state.glitchInterval = setInterval(() => {
                if (!this.state.isVisible) return;

                // Efeito glitch mais sutil
                if (Math.random() < 0.15) {
                    const glitchText = Array.from({ length: 3 }, () =>
                        glitchChars[Math.floor(Math.random() * glitchChars.length)]
                    ).join('');

                    this.elements.errorCode.textContent = glitchText;
                    this.elements.errorCode.style.color = '#dc3545';

                    // Restaurar após delay
                    setTimeout(() => {
                        this.elements.errorCode.textContent = originalText;
                        this.elements.errorCode.style.color = '';
                    }, 80);
                }
            }, 4000 + Math.random() * 2000);
        }

        startSparkleEffect() {
            this.state.sparkleInterval = setInterval(() => {
                if (!this.state.isVisible || this.state.isMobile) return;
                this.createSparkle();
            }, 3000 + Math.random() * 2000);
        }

        createSparkle() {
            const sparkleTypes = ['✨', '⭐', '💫'];
            const sparkle = document.createElement('div');

            sparkle.className = 'notfnd-dynamic-sparkle';
            sparkle.innerHTML = sparkleTypes[Math.floor(Math.random() * sparkleTypes.length)];

            // Posição aleatória
            const margin = 50;
            const maxX = window.innerWidth - margin;
            const maxY = window.innerHeight - margin;

            sparkle.style.cssText = `
                position: fixed;
                left: ${margin + Math.random() * (maxX - margin)}px;
                top: ${margin + Math.random() * (maxY - margin)}px;
                font-size: ${12 + Math.random() * 16}px;
                color: #ffc107;
                pointer-events: none;
                z-index: 5;
                animation: notfnd-sparkle-fade 2s ease-out forwards;
                opacity: 0.8;
            `;

            document.body.appendChild(sparkle);

            setTimeout(() => {
                if (sparkle.parentNode) {
                    sparkle.parentNode.removeChild(sparkle);
                }
            }, 2000);
        }

        startTypingEffect() {
            const title = document.querySelector('.notfnd-error-title');
            if (!title) return;

            const originalText = title.textContent;
            title.textContent = '';
            title.style.borderRight = '2px solid #ffc107';

            let i = 0;
            const typeInterval = setInterval(() => {
                if (i < originalText.length) {
                    title.textContent += originalText.charAt(i);
                    i++;
                } else {
                    clearInterval(typeInterval);
                    setTimeout(() => {
                        title.style.borderRight = 'none';
                    }, 1000);
                }
            }, 80);
        }

        pauseAnimations() {
            if (this.state.glitchInterval) {
                clearInterval(this.state.glitchInterval);
                this.state.glitchInterval = null;
            }
            if (this.state.sparkleInterval) {
                clearInterval(this.state.sparkleInterval);
                this.state.sparkleInterval = null;
            }
        }

        resumeAnimations() {
            if (this.state.animationsEnabled && this.state.isVisible) {
                this.startGlitchEffect();
                this.startSparkleEffect();
            }
        }

        optimizeForDevice() {
            if (this.state.isMobile) {
                // Reduzir animações em dispositivos móveis
                if (this.elements.floatingElements) {
                    this.elements.floatingElements.style.opacity = '0.4';
                }
                if (this.elements.particles) {
                    this.elements.particles.style.display = 'none';
                }
            } else {
                // Restaurar animações em desktop
                if (this.elements.floatingElements) {
                    this.elements.floatingElements.style.opacity = '';
                }
                if (this.elements.particles) {
                    this.elements.particles.style.display = '';
                }
            }
        }

        addClickFeedback(element) {
            // Feedback visual simples
            element.style.transform = 'scale(0.95)';
            element.style.transition = 'transform 0.1s ease';

            setTimeout(() => {
                element.style.transform = '';
            }, 100);

            // Som de feedback simples
            this.playClickFeedback();
        }

        // Estados de carregamento
        showLoadingState(message = 'Carregando...') {
            const existingOverlay = document.getElementById('notfnd-loading-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }

            const loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'notfnd-loading-overlay';
            loadingOverlay.innerHTML = `
                <div class="notfnd-loading-content">
                    <div class="notfnd-loading-spinner"></div>
                    <p class="notfnd-loading-text">${sanitizeInput(message)}</p>
                </div>
            `;

            loadingOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                backdrop-filter: blur(5px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                color: #ffc107;
                font-family: inherit;
                animation: notfnd-fadeIn 0.3s ease-out;
            `;

            document.body.appendChild(loadingOverlay);
        }

        hideLoadingState() {
            const overlay = document.getElementById('notfnd-loading-overlay');
            if (overlay) {
                overlay.style.animation = 'notfnd-fadeOut 0.3s ease-out';
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                }, 300);
            }
        }

        showErrorMessage(message, duration = 4000) {
            const errorMsg = document.createElement('div');
            errorMsg.className = 'notfnd-error-message';
            errorMsg.innerHTML = `
                <div class="notfnd-error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="notfnd-error-text">${sanitizeInput(message)}</div>
                <button class="notfnd-error-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            errorMsg.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #dc3545;
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 16px rgba(220, 53, 69, 0.3);
                z-index: 10000;
                animation: notfnd-slideInRight 0.4s ease;
                display: flex;
                align-items: center;
                gap: 1rem;
                max-width: 350px;
            `;

            document.body.appendChild(errorMsg);

            setTimeout(() => {
                if (errorMsg.parentNode) {
                    errorMsg.style.animation = 'notfnd-slideOutRight 0.4s ease';
                    setTimeout(() => {
                        if (errorMsg.parentNode) {
                            errorMsg.parentNode.removeChild(errorMsg);
                        }
                    }, 400);
                }
            }, duration);
        }

        // Feedback sonoro simples
        playHoverFeedback() {
            // Implementação básica de feedback sonoro
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) {
                    const audioContext = new AudioContext();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.setValueAtTime(600, audioContext.currentTime);
                    gainNode.gain.setValueAtTime(0.02, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.1);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.1);
                }
            } catch (e) {
                // Silenciosamente falha se áudio não estiver disponível
            }
        }

        playClickFeedback() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) {
                    const audioContext = new AudioContext();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                    gainNode.gain.setValueAtTime(0.05, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.1);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.1);
                }
            } catch (e) {
                // Silenciosamente falha
            }
        }

        // Analytics simplificado
        initializeAnalytics() {
            this.analytics = {
                sessionId: this.generateSessionId(),
                startTime: Date.now(),
                interactions: []
            };

            // Rastrear tempo na página
            setTimeout(() => {
                this.logInteraction('page_view_duration', { duration: '30_seconds' });
            }, NOTFND_CONFIG.ANALYTICS.SESSION_TIMEOUT);
        }

        generateSessionId() {
            return 'notfnd_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        logInteraction(type, data = {}) {
            const logEntry = {
                sessionId: this.analytics.sessionId,
                timestamp: new Date().toISOString(),
                type: type,
                data: data,
                url: window.location.href,
                sessionTime: Date.now() - this.state.userStartTime,
                interactionCount: this.state.interactionCount,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };

            this.analytics.interactions.push(logEntry);
            console.log('🏁 KR Legends 404 Interaction:', logEntry);
        }

        // Otimizações responsivas
        setupResponsiveOptimizations() {
            // Configurar observador de interseção para lazy loading
            if (this.features.intersectionObserver) {
                this.setupLazyLoading();
            }

            // Otimizar para dispositivo atual
            this.optimizeForDevice();
        }

        setupLazyLoading() {
            const lazyElements = document.querySelectorAll('.notfnd-help-section, .notfnd-racing-quote');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('notfnd-loaded');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px'
            });

            lazyElements.forEach(element => {
                observer.observe(element);
            });
        }

        setupIntersectionObserver() {
            // Observar elementos para animações baseadas em scroll
            const animatedElements = document.querySelectorAll('.notfnd-error-container');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'notfnd-fadeInUp 0.8s ease-out';
                    }
                });
            }, {
                threshold: 0.1
            });

            animatedElements.forEach(element => {
                observer.observe(element);
            });
        }

        // Utilitários
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        throttle(func, limit) {
            let inThrottle;
            return function () {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    }

    // CSS dinâmico para animações
    const advancedStyles = `
        @keyframes notfnd-sparkle-fade {
            0% {
                opacity: 0;
                transform: scale(0) rotate(0deg);
            }
            50% {
                opacity: 1;
                transform: scale(1.2) rotate(180deg);
            }
            100% {
                opacity: 0;
                transform: scale(0) rotate(360deg);
            }
        }

        @keyframes notfnd-slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes notfnd-slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @keyframes notfnd-fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes notfnd-fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        .notfnd-loading-content {
            text-align: center;
            max-width: 250px;
        }

        .notfnd-loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 193, 7, 0.3);
            border-top: 3px solid #ffc107;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        .notfnd-loading-text {
            font-size: 1rem;
            color: #ffc107;
        }

        .notfnd-error-message {
            border-left: 4px solid #fff;
        }

        .notfnd-error-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .notfnd-error-text {
            flex: 1;
            font-size: 0.9rem;
        }

        .notfnd-error-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .notfnd-error-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Lazy loading styles */
        .notfnd-help-section,
        .notfnd-racing-quote {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .notfnd-help-section.notfnd-loaded,
        .notfnd-racing-quote.notfnd-loaded {
            opacity: 1;
            transform: translateY(0);
        }
    `;

    // Adicionar estilos dinâmicos
    const styleSheet = document.createElement('style');
    styleSheet.textContent = advancedStyles;
    document.head.appendChild(styleSheet);

    // Funções globais para compatibilidade
    window.searchFor = function (term) {
        if (window.notFoundManager) {
            window.notFoundManager.performSearch(term);
        }
    };

    window.handleSearch = function (event) {
        event.preventDefault();
        const searchInput = document.getElementById('notfnd-search-input');
        if (searchInput && window.notFoundManager) {
            window.notFoundManager.performSearch(searchInput.value);
        }
        return false;
    };

    // Inicialização
    const initializeNotFoundPage = () => {
        window.notFoundManager = new NotFoundPageManager();
        console.log('🏁 KR Legends 404 Page fully initialized');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeNotFoundPage);
    } else {
        initializeNotFoundPage();
    }

    // Exportar para uso global
    window.KRNotFoundPageManager = NotFoundPageManager;

})();