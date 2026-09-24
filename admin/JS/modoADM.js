/**
 * Script principal para o painel de administração KR Legends
 * Gerencia a sidebar responsiva e interações do dashboard
 */

document.addEventListener('DOMContentLoaded', function () {
    // Elementos principais
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const body = document.body;

    // Verificar se os elementos existem antes de adicionar event listeners
    if (sidebarToggle && sidebar && sidebarOverlay) {
        // Toggle da sidebar em dispositivos móveis
        sidebarToggle.addEventListener('click', function () {
            toggleSidebar();
        });

        // Fechar sidebar ao clicar no overlay
        sidebarOverlay.addEventListener('click', function () {
            closeSidebar();
        });

        // Fechar sidebar com tecla ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // Gerenciar redimensionamento da janela
        window.addEventListener('resize', function () {
            handleWindowResize();
        });
    }

    // Inicializar tooltips do Bootstrap se existirem
    initializeTooltips();

    // Inicializar animações de contadores
    initializeCounterAnimations();

    // Atualizar estatísticas periodicamente (opcional)
    setInterval(updateStats, 300000); // A cada 5 minutos

    /**
     * Abre/fecha a sidebar
     */
    function toggleSidebar() {
        if (sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    /**
     * Abre a sidebar
     */
    function openSidebar() {
        sidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        body.classList.add('sidebar-open');

        // Atualizar aria-expanded para acessibilidade
        sidebarToggle.setAttribute('aria-expanded', 'true');
    }

    /**
     * Fecha a sidebar
     */
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        body.classList.remove('sidebar-open');

        // Atualizar aria-expanded para acessibilidade
        sidebarToggle.setAttribute('aria-expanded', 'false');
    }

    /**
     * Gerencia o redimensionamento da janela
     */
    function handleWindowResize() {
        // Fechar sidebar automaticamente em telas grandes
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    }

    /**
     * Inicializa tooltips do Bootstrap
     */
    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Anima os contadores de estatísticas
     */
    function initializeCounterAnimations() {
        const counters = document.querySelectorAll('.adm-stat-card-number, .adm-hero-stat-number');

        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        counters.forEach(counter => {
            observer.observe(counter);
        });
    }

    /**
     * Anima um contador específico
     * @param {Element} element - Elemento do contador
     */
    function animateCounter(element) {
        const target = parseInt(element.textContent.replace(/[^\d]/g, ''));
        const duration = 2000; // 2 segundos
        const step = target / (duration / 16); // 60 FPS
        let current = 0;

        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }

            // Formatar o número com separadores de milhares
            element.textContent = Math.floor(current).toLocaleString('pt-PT');
        }, 16);
    }

    /**
     * Atualiza as estatísticas (função placeholder para futuras implementações)
     */
    function updateStats() {
        // Esta função pode ser implementada para atualizar estatísticas via AJAX
        console.log('Atualizando estatísticas...');

        // Exemplo de implementação:
        /*
        fetch('api/stats.php')
            .then(response => response.json())
            .then(data => {
                // Atualizar elementos da página com novos dados
                updateStatElements(data);
            })
            .catch(error => {
                console.error('Erro ao atualizar estatísticas:', error);
            });
        */
    }

    /**
     * Atualiza elementos de estatística na página
     * @param {Object} data - Dados das estatísticas
     */
    function updateStatElements(data) {
        // Implementar atualização dos elementos conforme necessário
        if (data.totalUsers) {
            const totalUsersElement = document.querySelector('[data-stat="total-users"]');
            if (totalUsersElement) {
                totalUsersElement.textContent = data.totalUsers.toLocaleString('pt-PT');
            }
        }

        // Adicionar mais atualizações conforme necessário
    }

    /**
     * Adiciona funcionalidade de busca na sidebar (futuro)
     */
    function initializeSidebarSearch() {
        const searchInput = document.getElementById('sidebarSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                const searchTerm = e.target.value.toLowerCase();
                const sidebarItems = document.querySelectorAll('.adm-sidebar-item');

                sidebarItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const parent = item.closest('.adm-sidebar-section');

                    if (text.includes(searchTerm)) {
                        item.style.display = 'flex';
                        if (parent) parent.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Esconder seções vazias
                const sections = document.querySelectorAll('.adm-sidebar-section');
                sections.forEach(section => {
                    const visibleItems = section.querySelectorAll('.adm-sidebar-item[style*="flex"]');
                    section.style.display = visibleItems.length > 0 ? 'block' : 'none';
                });
            });
        }
    }

    /**
     * Gerencia o estado ativo dos itens da sidebar
     */
    function manageSidebarActiveState() {
        const currentPath = window.location.pathname;
        const sidebarItems = document.querySelectorAll('.adm-sidebar-item');

        sidebarItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href)) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Inicializar estado ativo da sidebar
    manageSidebarActiveState();

    /**
     * Adiciona funcionalidade de notificações (futuro)
     */
    function initializeNotifications() {
        // Implementar sistema de notificações para o painel admin
        console.log('Sistema de notificações inicializado');
    }

    /**
     * Gerencia temas (futuro)
     */
    function initializeThemeManager() {
        // Implementar alternância de temas se necessário
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                document.body.classList.toggle('light-theme');
                localStorage.setItem('adminTheme',
                    document.body.classList.contains('light-theme') ? 'light' : 'dark'
                );
            });

            // Carregar tema salvo
            const savedTheme = localStorage.getItem('adminTheme');
            if (savedTheme === 'light') {
                document.body.classList.add('light-theme');
            }
        }
    }

    // Log de inicialização
    console.log('Painel de administração KR Legends inicializado com sucesso');
});

/**
 * Função utilitária para debounce
 * @param {Function} func - Função a ser executada
 * @param {number} wait - Tempo de espera em ms
 * @param {boolean} immediate - Executar imediatamente
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

/**
 * Função utilitária para formatação de números
 * @param {number} num - Número a ser formatado
 * @returns {string} - Número formatado
 */
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

/**
 * Função para mostrar mensagens de feedback
 * @param {string} message - Mensagem a ser exibida
 * @param {string} type - Tipo da mensagem (success, error, warning, info)
 */
function showMessage(message, type = 'info') {
    // Implementar sistema de mensagens toast
    console.log(`${type.toUpperCase()}: ${message}`);

    // Exemplo de implementação com toast do Bootstrap
    /*
    const toastContainer = document.getElementById('toastContainer');
    if (toastContainer) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remover o toast após ser escondido
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    */
}