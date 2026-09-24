/*--------------------------------------------------------------
Discord Widget - JavaScript
--------------------------------------------------------------*/

class DiscordWidget {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.handleIframeLoad();
        this.addAnimations();
    }

    setupEventListeners() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeWidget();
            });
        } else {
            this.initializeWidget();
        }

        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));
    }

    initializeWidget() {
        const widgetCard = document.querySelector('.discord-widget-card');
        const iframe = document.querySelector('.discord-iframe');

        if (widgetCard && iframe) {
            this.observeWidget(widgetCard);
            this.setupIframeErrorHandling(iframe);
        }
    }

    handleIframeLoad() {
        const iframe = document.querySelector('.discord-iframe');
        const loadingElement = document.querySelector('.discord-loading');

        if (iframe) {
            iframe.addEventListener('load', () => {
                if (loadingElement) {
                    loadingElement.style.display = 'none';
                }
                iframe.style.opacity = '1';
                this.addLoadedClass();
            });

            iframe.addEventListener('error', () => {
                this.handleIframeError();
            });
        }
    }

    setupIframeErrorHandling(iframe) {
        setTimeout(() => {
            if (iframe.style.opacity !== '1') {
                this.handleIframeError();
            }
        }, 10000);
    }

    handleIframeError() {
        const container = document.querySelector('.discord-iframe-container');
        if (container) {
            container.innerHTML = `
                <div class="discord-error-message">
                    <div class="discord-error-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h4>Erro ao carregar o Discord</h4>
                    <p>Não foi possível carregar o widget do Discord. Tenta novamente mais tarde.</p>
                    <a href="https://discord.gg/krlegends" target="_blank" class="discord-cta-button">
                        <i class="bi bi-discord"></i>
                        Abrir Discord Diretamente
                    </a>
                </div>
            `;
        }
    }

    addLoadedClass() {
        const widgetCard = document.querySelector('.discord-widget-card');
        if (widgetCard) {
            widgetCard.classList.add('discord-loaded');
        }
    }

    observeWidget(element) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('discord-animate-in');
                    this.animateFeatures();
                }
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -50px 0px'
        });

        observer.observe(element);
    }

    animateFeatures() {
        const features = document.querySelectorAll('.discord-feature');
        features.forEach((feature, index) => {
            setTimeout(() => {
                feature.classList.add('discord-feature-animate');
            }, index * 100);
        });
    }

    addAnimations() {
        const style = document.createElement('style');
        style.textContent = `
            .discord-error-message {
                text-align: center;
                padding: 3rem 2rem;
                color: var(--color-white);
            }
            
            .discord-error-icon {
                font-size: 3rem;
                color: var(--color-yellow);
                margin-bottom: 1rem;
            }
            
            .discord-error-message h4 {
                color: var(--color-yellow);
                margin-bottom: 1rem;
            }
            
            .discord-error-message p {
                color: var(--color-gray-400);
                margin-bottom: 2rem;
            }
        `;
        document.head.appendChild(style);
    }

    handleResize() {
        const iframe = document.querySelector('.discord-iframe');
        if (iframe && window.innerWidth <= 768) {
            iframe.style.height = '400px';
        } else if (iframe) {
            iframe.style.height = '500px';
        }
    }

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

    reinitialize() {
        this.init();
    }

    trackEvent(eventName, properties = {}) {
        console.log(`Discord Widget Event: ${eventName}`, properties);
    }
}

const discordWidget = new DiscordWidget();
window.DiscordWidget = DiscordWidget;