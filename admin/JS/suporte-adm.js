/**
 * Suporte ADM - JavaScript
 * Funcionalidades para a página de administração de pedidos de suporte
 */

document.addEventListener('DOMContentLoaded', function () {
    initializeSupportAdmin();
});

/**
 * Inicializa todas as funcionalidades da página
 */
function initializeSupportAdmin() {
    // Inicializar tooltips do Bootstrap se existirem
    initializeTooltips();

    // Configurar eventos de filtros
    setupFilterEvents();

    // Configurar sidebar responsiva
    setupResponsiveSidebar();

    // Configurar filtros dinâmicos
    setupDynamicFilters();

    console.log('Support Admin initialized');
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
 * Configura eventos dos filtros
 */
function setupFilterEvents() {
    const filterToggle = document.getElementById('filterToggle');
    const advancedFilters = document.getElementById('advancedFilters');

    if (filterToggle && advancedFilters) {
        filterToggle.addEventListener('click', function (e) {
            e.preventDefault();
            const isVisible = advancedFilters.style.display !== 'none';
            advancedFilters.style.display = isVisible ? 'none' : 'block';

            // Atualizar ícone do botão
            const icon = filterToggle.querySelector('i');
            if (icon) {
                icon.className = isVisible ? 'bi bi-funnel me-1' : 'bi bi-funnel-fill me-1';
            }
        });
    }

    // Limpar filtros
    const clearButton = document.querySelector('.sprtadm-btn-secondary[href*="suporte-adm.php"]');
    if (clearButton) {
        clearButton.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = 'suporte-adm.php';
        });
    }
}

/**
 * Configura filtros dinâmicos baseados no contexto
 */
function setupDynamicFilters() {
    const errorContextSelect = document.getElementById('errorContext');
    const errorTypeSelect = document.getElementById('errorType');

    if (errorContextSelect && errorTypeSelect) {
        // Configurar opções iniciais
        updateErrorTypeOptions(errorContextSelect.value);

        // Listener para mudanças no contexto
        errorContextSelect.addEventListener('change', function () {
            updateErrorTypeOptions(this.value);
        });
    }
}

/**
 * Atualiza as opções de tipo de erro baseado no contexto
 * @param {string} context - Contexto selecionado (website/game)
 */
function updateErrorTypeOptions(context) {
    const errorTypeSelect = document.getElementById('errorType');
    if (!errorTypeSelect) return;

    // Salvar seleções atuais
    const currentSelections = Array.from(errorTypeSelect.selectedOptions).map(option => option.value);

    // Limpar opções existentes
    errorTypeSelect.innerHTML = '';

    let options = '';

    if (context === 'website') {
        options += '<option value="responsividade_visual">Responsividade Visual</option>';
        options += '<option value="funcionalidade">Funcionalidade</option>';
        options += '<option value="latencia">Latência</option>';
        options += '<option value="login_registo">Login ou Registo</option>';
    } else if (context === 'game') {
        options += '<option value="visual_grafico">Visual/Gráfico</option>';
        options += '<option value="jogabilidade">Jogabilidade</option>';
        options += '<option value="desempenho">Desempenho</option>';
        options += '<option value="conexao">Conexão</option>';
    } else {
        // Mostrar todas as opções se nenhum contexto for selecionado
        options += '<option value="responsividade_visual">Responsividade Visual</option>';
        options += '<option value="funcionalidade">Funcionalidade</option>';
        options += '<option value="latencia">Latência</option>';
        options += '<option value="login_registo">Login ou Registo</option>';
        options += '<option value="visual_grafico">Visual/Gráfico</option>';
        options += '<option value="jogabilidade">Jogabilidade</option>';
        options += '<option value="desempenho">Desempenho</option>';
        options += '<option value="conexao">Conexão</option>';
    }

    errorTypeSelect.innerHTML = options;

    // Restaurar seleções que ainda são válidas
    Array.from(errorTypeSelect.options).forEach(option => {
        if (currentSelections.includes(option.value)) {
            option.selected = true;
        }
    });
}

/**
 * Configura sidebar responsiva
 */
function setupResponsiveSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !overlay) return;

    // Toggle sidebar em dispositivos móveis
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
    }

    // Fechar sidebar ao clicar no overlay
    overlay.addEventListener('click', function () {
        if (sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });

    // Fechar sidebar com ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });

    // Adicionar botão de toggle se não existir
    if (!document.querySelector('.sidebar-toggle')) {
        createSidebarToggle(toggleSidebar);
    }
}

/**
 * Cria botão de toggle da sidebar para dispositivos móveis
 */
function createSidebarToggle(toggleFunction) {
    const toggleButton = document.createElement('button');
    toggleButton.className = 'sidebar-toggle d-lg-none';
    toggleButton.innerHTML = '<i class="bi bi-list"></i>';
    toggleButton.setAttribute('aria-label', 'Toggle sidebar');

    toggleButton.addEventListener('click', toggleFunction);

    // Adicionar ao cabeçalho da página
    const header = document.querySelector('.sprtadm-header');
    if (header) {
        header.insertBefore(toggleButton, header.firstChild);
    }
}

/**
 * Alterna o status de um pedido de suporte
 * @param {number} requestId - ID do pedido
 * @param {string} currentStatus - Status atual
 */
function toggleStatus(requestId, currentStatus) {
    // Definir próximo status
    const statusFlow = {
        'nao_resolvido': 'em_processo',
        'em_processo': 'resolvido',
        'resolvido': 'nao_resolvido'
    };

    const newStatus = statusFlow[currentStatus];

    if (!newStatus) {
        console.error('Status inválido:', currentStatus);
        return;
    }

    // Mostrar loading
    const button = document.querySelector(`[data-request-id="${requestId}"] .sprtadm-status-btn`);
    if (button) {
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Atualizando...';
        button.disabled = true;
    }

    // Fazer requisição AJAX
    fetch('suporte-update-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            request_id: requestId,
            new_status: newStatus
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar interface
                updateStatusButton(requestId, newStatus);
                showNotification('Status atualizado com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao atualizar status');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao atualizar status: ' + error.message, 'error');

            // Restaurar botão
            if (button) {
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        });
}

/**
 * Atualiza o botão de status na interface
 * @param {number} requestId - ID do pedido
 * @param {string} newStatus - Novo status
 */
function updateStatusButton(requestId, newStatus) {
    const card = document.querySelector(`[data-request-id="${requestId}"]`);
    if (!card) return;

    const button = card.querySelector('.sprtadm-status-btn');
    if (!button) return;

    // Remover classes antigas
    button.className = button.className.replace(/sprtadm-status-\w+/g, '');

    // Adicionar nova classe
    button.classList.add(`sprtadm-status-${newStatus}`);

    // Atualizar conteúdo
    const statusLabels = {
        'nao_resolvido': { icon: 'exclamation-triangle', text: 'Não Resolvido' },
        'em_processo': { icon: 'clock', text: 'Em Processo' },
        'resolvido': { icon: 'check-circle', text: 'Resolvido' }
    };

    const statusInfo = statusLabels[newStatus];
    if (statusInfo) {
        button.innerHTML = `<i class="bi bi-${statusInfo.icon}"></i> ${statusInfo.text}`;
        button.onclick = () => toggleStatus(requestId, newStatus);
    }

    button.disabled = false;
}

/**
 * Confirma a exclusão de um pedido de suporte
 * @param {number} requestId - ID do pedido
 * @param {string} title - Título do pedido
 */
function confirmDelete(requestId, title) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteInfo = document.getElementById('deleteRequestInfo');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    // Atualizar informações do modal
    deleteInfo.innerHTML = `
        <strong>ID:</strong> #${requestId}<br>
        <strong>Título:</strong> ${title}
    `;

    // Configurar botão de confirmação
    confirmBtn.onclick = () => deleteRequest(requestId);

    modal.show();
}

/**
 * Exclui um pedido de suporte
 * @param {number} requestId - ID do pedido
 */
function deleteRequest(requestId) {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const originalContent = confirmBtn.innerHTML;

    // Mostrar loading
    confirmBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Excluindo...';
    confirmBtn.disabled = true;

    // Fazer requisição AJAX
    fetch('suporte-delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            request_id: requestId
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Fechar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                modal.hide();

                // Remover card da interface
                const card = document.querySelector(`[data-request-id="${requestId}"]`);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        card.remove();
                        updateRequestCount();
                    }, 300);
                }

                showNotification('Pedido excluído com sucesso!', 'success');
            } else {
                throw new Error(data.message || 'Erro ao excluir pedido');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao excluir pedido: ' + error.message, 'error');
        })
        .finally(() => {
            // Restaurar botão
            confirmBtn.innerHTML = originalContent;
            confirmBtn.disabled = false;
        });
}

/**
 * Atualiza o contador de pedidos na interface
 */
function updateRequestCount() {
    const cards = document.querySelectorAll('.sprtadm-request-card');
    const countElement = document.querySelector('.sprtadm-section-title');

    if (countElement) {
        const currentText = countElement.textContent;
        const newText = currentText.replace(/\(\d+\)/, `(${cards.length})`);
        countElement.textContent = newText;
    }

    // Se não há mais pedidos, mostrar estado vazio
    if (cards.length === 0) {
        const grid = document.querySelector('.sprtadm-requests-grid');
        if (grid) {
            grid.innerHTML = `
                <div class="sprtadm-empty-state">
                    <i class="bi bi-inbox"></i>
                    <h3>Nenhum pedido encontrado</h3>
                    <p>Não foram encontrados pedidos de suporte com os filtros aplicados.</p>
                </div>
            `;
        }
    }
}

/**
 * Mostra notificação para o usuário
 * @param {string} message - Mensagem
 * @param {string} type - Tipo (success, error, warning, info)
 */
function showNotification(message, type = 'info') {
    // Criar elemento de notificação
    const notification = document.createElement('div');
    notification.className = `sprtadm-notification sprtadm-notification-${type}`;
    notification.innerHTML = `
        <div class="sprtadm-notification-content">
            <i class="bi bi-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="sprtadm-notification-close" onclick="this.parentElement.remove()">
            <i class="bi bi-x"></i>
        </button>
    `;

    // Adicionar estilos se não existirem
    if (!document.querySelector('#notification-styles')) {
        addNotificationStyles();
    }

    // Adicionar ao DOM
    document.body.appendChild(notification);

    // Remover automaticamente após 5 segundos
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);

    // Animar entrada
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
}

/**
 * Retorna o ícone apropriado para o tipo de notificação
 * @param {string} type - Tipo da notificação
 * @returns {string} - Nome do ícone
 */
function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-triangle',
        'warning': 'exclamation-circle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Adiciona estilos para notificações
 */
function addNotificationStyles() {
    const styles = document.createElement('style');
    styles.id = 'notification-styles';
    styles.textContent = `
        .sprtadm-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--color-gray-900);
            border: 1px solid var(--color-gray-700);
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        .sprtadm-notification.show {
            transform: translateX(0);
        }
        
        .sprtadm-notification-success {
            border-left: 4px solid #198754;
        }
        
        .sprtadm-notification-error {
            border-left: 4px solid #dc3545;
        }
        
        .sprtadm-notification-warning {
            border-left: 4px solid #fd7e14;
        }
        
        .sprtadm-notification-info {
            border-left: 4px solid #0dcaf0;
        }
        
        .sprtadm-notification-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            color: var(--color-white);
        }
        
        .sprtadm-notification-content i {
            color: var(--color-yellow);
        }
        
        .sprtadm-notification-close {
            background: none;
            border: none;
            color: var(--color-gray-400);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: color 0.2s ease;
        }
        
        .sprtadm-notification-close:hover {
            color: var(--color-white);
        }
        
        @media (max-width: 768px) {
            .sprtadm-notification {
                right: 10px;
                left: 10px;
                min-width: auto;
                max-width: none;
            }
        }
    `;
    document.head.appendChild(styles);
}

/**
 * Função para filtrar pedidos em tempo real (opcional)
 * @param {string} searchTerm - Termo de busca
 */
function filterRequests(searchTerm) {
    const cards = document.querySelectorAll('.sprtadm-request-card');
    const term = searchTerm.toLowerCase();

    cards.forEach(card => {
        const title = card.querySelector('.sprtadm-card-title')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.sprtadm-card-description')?.textContent.toLowerCase() || '';
        const id = card.getAttribute('data-request-id') || '';

        const matches = title.includes(term) || description.includes(term) || id.includes(term);

        card.style.display = matches ? 'block' : 'none';
    });
}

/**
 * Função para exportar dados (futura implementação)
 */
function exportData(format = 'csv') {
    console.log('Export functionality to be implemented:', format);
    showNotification('Funcionalidade de exportação em desenvolvimento', 'info');
}

/**
 * Função para atualizar estatísticas em tempo real
 */
function updateStatistics() {
    fetch('get-statistics.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar cards de estatísticas
                updateStatCard('total', data.stats.total);
                updateStatCard('nao_resolvido', data.stats.nao_resolvido);
                updateStatCard('em_processo', data.stats.em_processo);
                updateStatCard('resolvido', data.stats.resolvido);
            }
        })
        .catch(error => {
            console.error('Erro ao atualizar estatísticas:', error);
        });
}

/**
 * Atualiza um card de estatística específico
 * @param {string} type - Tipo da estatística
 * @param {number} value - Novo valor
 */
function updateStatCard(type, value) {
    const cards = document.querySelectorAll('.sprtadm-stat-card');
    // Implementar lógica de atualização baseada no tipo
    // Esta função pode ser expandida conforme necessário
}

// Expor funções globalmente para uso em onclick handlers
window.toggleStatus = toggleStatus;
window.showNotification = showNotification;
window.filterRequests = filterRequests;
window.exportData = exportData;
window.confirmDelete = confirmDelete;
window.deleteRequest = deleteRequest;