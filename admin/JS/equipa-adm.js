/**
 * Gestão da Equipa - JavaScript
 * Sistema de administração para membros da equipa
 */

// ================================
// VARIÁVEIS GLOBAIS
// ================================
let currentPage = 1;
const itemsPerPage = 40;
let totalItems = 0;
let filteredItems = 0;
let allMembers = [];
let filteredMembers = [];

// ================================
// INICIALIZAÇÃO
// ================================
document.addEventListener('DOMContentLoaded', function () {
    initializeTeamAdmin();
});

function initializeTeamAdmin() {
    // Capturar todos os membros da tabela
    captureAllMembers();

    // Configurar event listeners
    setupEventListeners();

    // Aplicar filtros iniciais
    applyFiltersAndPagination();

    // Atualizar estatísticas
    updateStats();

    console.log('Sistema de gestão da equipa inicializado');
}

// ================================
// CAPTURA DE DADOS
// ================================
function captureAllMembers() {
    const tbody = document.querySelector('#membersTable tbody');
    const rows = tbody.querySelectorAll('tr');

    allMembers = [];

    rows.forEach(row => {
        // Verificar se não é uma linha de "nenhum membro encontrado"
        if (row.cells.length > 1) {
            const member = {
                id: parseInt(row.querySelector('.cell-id')?.textContent.trim()) || 0,
                nome: row.querySelector('.cell-nome')?.textContent.trim() || '',
                funcao: row.querySelector('.cell-funcao')?.textContent.trim() || '',
                bio: row.cells[4]?.textContent.trim() || '',
                quote: row.cells[5]?.textContent.trim() || '',
                imagem: row.querySelector('img.eqadm-avatar')?.getAttribute('src') || '',
                element: row.cloneNode(true)
            };

            // Extrair extensão da imagem
            if (member.imagem) {
                const parts = member.imagem.split('.');
                member.imageExt = parts[parts.length - 1].toLowerCase().split(/[?#]/)[0];
            } else {
                member.imageExt = '';
            }

            allMembers.push(member);
        }
    });

    totalItems = allMembers.length;
    console.log(`Capturados ${totalItems} membros`);
}

// ================================
// EVENT LISTENERS
// ================================
function setupEventListeners() {
    // Toggle do painel de filtros
    const toggleFilters = document.getElementById('toggleFilters');
    if (toggleFilters) {
        toggleFilters.addEventListener('click', toggleFiltersPanel);
    }

    // Pesquisa em tempo real
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Filtros
    const filterRole = document.getElementById('filterRole');
    const filterOrder = document.getElementById('filterOrder');
    const filterExt = document.getElementById('filterExt');

    if (filterRole) filterRole.addEventListener('change', applyFiltersAndPagination);
    if (filterOrder) filterOrder.addEventListener('change', applyFiltersAndPagination);
    if (filterExt) filterExt.addEventListener('change', applyFiltersAndPagination);

    // Modais
    setupModalEventListeners();
}

function setupModalEventListeners() {
    // Fechar modais ao clicar no backdrop
    window.addEventListener('click', function (e) {
        if (e.target.classList.contains('eqadm-modal-backdrop')) {
            closeAddModal();
            closeEditModal();
        }
    });

    // Fechar modais com ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });
}

// ================================
// FUNÇÕES DE MODAL
// ================================
function openAddModal() {
    const modal = document.getElementById('modalAdd');
    if (modal) {
        modal.classList.add('eqadm-modal-show');
        document.body.style.overflow = 'hidden';

        // Focar no primeiro campo
        const firstInput = modal.querySelector('input[type="text"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeAddModal() {
    const modal = document.getElementById('modalAdd');
    if (modal) {
        modal.classList.remove('eqadm-modal-show');
        document.body.style.overflow = '';

        // Limpar formulário
        const form = modal.querySelector('form');
        if (form) form.reset();
    }
}

function openEditModal(id, nome, funcao, bio, quote, linkedin, email, instagram, youtube) {
    // Preencher campos do modal de edição
    const fields = {
        'edit_eq_id': id,
        'edit_eq_nome': nome,
        'edit_eq_funcao': funcao,
        'edit_eq_bio': bio,
        'edit_eq_quote': quote,
        'edit_eq_linkedin': linkedin,
        'edit_eq_email': email,
        'edit_eq_instagram': instagram,
        'edit_eq_youtube': youtube
    };

    Object.entries(fields).forEach(([fieldId, value]) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = value || '';
        }
    });

    // Mostrar modal
    const modal = document.getElementById('modalEdit');
    if (modal) {
        modal.classList.add('eqadm-modal-show');
        document.body.style.overflow = 'hidden';

        // Focar no primeiro campo
        const firstInput = modal.querySelector('input[type="text"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeEditModal() {
    const modal = document.getElementById('modalEdit');
    if (modal) {
        modal.classList.remove('eqadm-modal-show');
        document.body.style.overflow = '';
    }
}

// ================================
// FILTROS E PESQUISA
// ================================
function toggleFiltersPanel() {
    const panel = document.getElementById('filterPanel');
    const button = document.getElementById('toggleFilters');

    if (panel && button) {
        const isVisible = panel.style.display !== 'none';

        if (isVisible) {
            panel.style.display = 'none';
            button.innerHTML = '<i class="bi bi-funnel"></i>';
            button.title = 'Mostrar filtros';
        } else {
            panel.style.display = 'block';
            button.innerHTML = '<i class="bi bi-funnel-fill"></i>';
            button.title = 'Ocultar filtros';
        }
    }
}

function handleSearch() {
    applyFiltersAndPagination();
}

function applyFiltersAndPagination() {
    // Obter valores dos filtros
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const selectedRole = document.getElementById('filterRole')?.value.toLowerCase() || '';
    const order = document.getElementById('filterOrder')?.value || 'asc';
    const extFilter = document.getElementById('filterExt')?.value.toLowerCase() || '';

    // Filtrar membros
    filteredMembers = allMembers.filter(member => {
        // Pesquisa por ID ou Nome
        const matchesSearch = member.id.toString().includes(searchTerm) ||
            member.nome.toLowerCase().includes(searchTerm);

        // Filtro por função
        const matchesRole = selectedRole === '' ||
            member.funcao.toLowerCase() === selectedRole;

        // Filtro por extensão de imagem
        const matchesExt = extFilter === '' ||
            member.imageExt === extFilter;

        return matchesSearch && matchesRole && matchesExt;
    });

    // Ordenar membros
    filteredMembers.sort((a, b) => {
        return order === 'asc' ? a.id - b.id : b.id - a.id;
    });

    filteredItems = filteredMembers.length;

    // Resetar para primeira página
    currentPage = 1;

    // Aplicar paginação
    updateTableDisplay();
    updatePagination();
    updateStats();

    console.log(`Filtros aplicados: ${filteredItems} membros encontrados`);
}

// ================================
// PAGINAÇÃO
// ================================
function updateTableDisplay() {
    const tbody = document.querySelector('#membersTable tbody');
    if (!tbody) return;

    // Limpar tabela
    tbody.innerHTML = '';

    // Verificar se há membros filtrados
    if (filteredMembers.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="11" class="eqadm-text-center" style="padding: 2rem; color: var(--color-gray-400);">
                <i class="bi bi-search" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                Nenhum membro encontrado com os critérios selecionados.
            </td>
        `;
        tbody.appendChild(emptyRow);
        return;
    }

    // Calcular índices da página atual
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredMembers.length);

    // Mostrar membros da página atual
    for (let i = startIndex; i < endIndex; i++) {
        const member = filteredMembers[i];
        tbody.appendChild(member.element.cloneNode(true));
    }

    // Reconfigurar event listeners dos botões de ação
    setupTableActionListeners();
}

function setupTableActionListeners() {
    // Reconfigurar botões de editar (se necessário)
    // Os botões já têm onclick inline, mas podemos adicionar melhorias aqui
    const editButtons = document.querySelectorAll('[onclick*="openEditModal"]');
    editButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // Adicionar feedback visual
            button.style.transform = 'scale(0.95)';
            setTimeout(() => {
                button.style.transform = '';
            }, 150);
        });
    });

    // Melhorar botões de eliminar
    const deleteButtons = document.querySelectorAll('a[href*="delete_id"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const memberName = this.closest('tr').querySelector('.cell-nome')?.textContent.trim();
            const confirmMessage = `Tem certeza que deseja eliminar o membro "${memberName}"?\n\nEsta ação não pode ser desfeita.`;

            if (confirm(confirmMessage)) {
                // Adicionar loading
                button.innerHTML = '<div class="eqadm-loading"></div>';
                button.style.pointerEvents = 'none';

                // Redirecionar após um pequeno delay para mostrar o loading
                setTimeout(() => {
                    window.location.href = this.href;
                }, 500);
            }
        });
    });
}

function updatePagination() {
    const totalPages = Math.ceil(filteredItems / itemsPerPage);
    const paginationContainer = document.querySelector('.eqadm-pagination');

    // Remover paginação existente se houver
    if (paginationContainer) {
        paginationContainer.remove();
    }

    // Não mostrar paginação se houver apenas uma página ou nenhum item
    if (totalPages <= 1) {
        return;
    }

    // Criar nova paginação
    const pagination = createPaginationElement(totalPages);

    // Inserir após a tabela
    const tableContainer = document.querySelector('.eqadm-table-container');
    if (tableContainer) {
        tableContainer.insertAdjacentElement('afterend', pagination);
    }
}

function createPaginationElement(totalPages) {
    const pagination = document.createElement('div');
    pagination.className = 'eqadm-pagination';

    let paginationHTML = '';

    // Botão "Anterior"
    const prevDisabled = currentPage === 1 ? 'disabled' : '';
    paginationHTML += `
        <button class="eqadm-pagination-btn" onclick="changePage(${currentPage - 1})" ${prevDisabled}>
            <i class="bi bi-chevron-left"></i>
        </button>
    `;

    // Números das páginas
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    // Primeira página (se não estiver no range)
    if (startPage > 1) {
        paginationHTML += `<button class="eqadm-pagination-btn" onclick="changePage(1)">1</button>`;
        if (startPage > 2) {
            paginationHTML += `<span class="eqadm-pagination-btn" style="border: none; background: transparent;">...</span>`;
        }
    }

    // Páginas do range atual
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentPage ? 'active' : '';
        paginationHTML += `
            <button class="eqadm-pagination-btn ${activeClass}" onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }

    // Última página (se não estiver no range)
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<span class="eqadm-pagination-btn" style="border: none; background: transparent;">...</span>`;
        }
        paginationHTML += `<button class="eqadm-pagination-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
    }

    // Botão "Próximo"
    const nextDisabled = currentPage === totalPages ? 'disabled' : '';
    paginationHTML += `
        <button class="eqadm-pagination-btn" onclick="changePage(${currentPage + 1})" ${nextDisabled}>
            <i class="bi bi-chevron-right"></i>
        </button>
    `;

    // Informações da paginação
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, filteredItems);

    paginationHTML += `
        <div class="eqadm-pagination-info">
            Mostrando ${startItem}-${endItem} de ${filteredItems} membros
        </div>
    `;

    pagination.innerHTML = paginationHTML;
    return pagination;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredItems / itemsPerPage);

    if (page < 1 || page > totalPages) {
        return;
    }

    currentPage = page;
    updateTableDisplay();
    updatePagination();

    // Scroll suave para o topo da tabela
    const tableContainer = document.querySelector('.eqadm-table-container');
    if (tableContainer) {
        tableContainer.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    console.log(`Página alterada para: ${currentPage}`);
}

// ================================
// ESTATÍSTICAS
// ================================
function updateStats() {
    // Criar ou atualizar painel de estatísticas
    let statsContainer = document.querySelector('.eqadm-stats');

    if (!statsContainer) {
        statsContainer = document.createElement('div');
        statsContainer.className = 'eqadm-stats';

        // Inserir após o cabeçalho
        const header = document.querySelector('.eqadm-header') ||
            document.querySelector('.eqadm-title').parentElement;
        if (header) {
            header.insertAdjacentElement('afterend', statsContainer);
        }
    }

    // Calcular estatísticas
    const totalMembers = allMembers.length;
    const filteredCount = filteredMembers.length;
    const uniqueRoles = [...new Set(allMembers.map(m => m.funcao))].length;
    const membersWithImages = allMembers.filter(m =>
        m.imagem && !m.imagem.includes('member_default.png')
    ).length;

    // Atualizar HTML das estatísticas
    statsContainer.innerHTML = `
        <div class="eqadm-stat-card">
            <div class="eqadm-stat-number">${totalMembers}</div>
            <div class="eqadm-stat-label">Total de Membros</div>
        </div>
        <div class="eqadm-stat-card">
            <div class="eqadm-stat-number">${filteredCount}</div>
            <div class="eqadm-stat-label">Membros Filtrados</div>
        </div>
        <div class="eqadm-stat-card">
            <div class="eqadm-stat-number">${uniqueRoles}</div>
            <div class="eqadm-stat-label">Funções Únicas</div>
        </div>
        <div class="eqadm-stat-card">
            <div class="eqadm-stat-number">${membersWithImages}</div>
            <div class="eqadm-stat-label">Com Foto Personalizada</div>
        </div>
    `;
}

// ================================
// UTILITÁRIOS
// ================================
function debounce(func, wait) {
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

// ================================
// MELHORIAS DE UX
// ================================

// Adicionar loading states aos formulários
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form.closest('.eqadm-modal-content')) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.innerHTML = '<div class="eqadm-loading"></div> A processar...';
            submitButton.disabled = true;
        }
    }
});

// Melhorar feedback visual dos botões
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('eqadm-btn')) {
        e.target.style.transform = 'scale(0.98)';
        setTimeout(() => {
            e.target.style.transform = '';
        }, 150);
    }
});

// Auto-hide de mensagens de feedback
document.addEventListener('DOMContentLoaded', function () {
    const feedbackMessages = document.querySelectorAll('.eqadm-feedback-success, .eqadm-feedback-error');
    feedbackMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });
});

// Validação em tempo real dos formulários
function setupFormValidation() {
    const requiredFields = document.querySelectorAll('input[required], textarea[required]');

    requiredFields.forEach(field => {
        field.addEventListener('blur', function () {
            if (!this.value.trim()) {
                this.style.borderColor = 'var(--color-danger)';
            } else {
                this.style.borderColor = 'var(--color-gray-600)';
            }
        });

        field.addEventListener('input', function () {
            if (this.value.trim()) {
                this.style.borderColor = 'var(--color-gray-600)';
            }
        });
    });
}

// Inicializar validação quando os modais abrem
document.addEventListener('DOMContentLoaded', function () {
    setupFormValidation();
});

console.log('Sistema de gestão da equipa carregado com sucesso');