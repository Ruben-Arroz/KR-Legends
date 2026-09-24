/**
 * Gestão de Utilizadores - JavaScript
 * Sistema de administração para utilizadores da plataforma
 */

// ================================
// VARIÁVEIS GLOBAIS
// ================================
let currentPage = 1;
const itemsPerPage = 40;
let totalItems = 0;
let filteredItems = 0;
let allUsers = [];
let filteredUsers = [];

// ================================
// INICIALIZAÇÃO
// ================================
document.addEventListener('DOMContentLoaded', function () {
    initializeUsersAdmin();
});

function initializeUsersAdmin() {
    // Capturar todos os utilizadores da tabela
    captureAllUsers();

    // Configurar event listeners
    setupEventListeners();

    // Aplicar filtros iniciais
    applyFiltersAndPagination();

    // Atualizar estatísticas
    updateStats();

    console.log('Sistema de gestão de utilizadores inicializado');
}

// ================================
// CAPTURA DE DADOS
// ================================
function captureAllUsers() {
    const tbody = document.querySelector('#usersTable tbody');
    const rows = tbody.querySelectorAll('tr');

    allUsers = [];

    rows.forEach(row => {
        // Verificar se não é uma linha de "nenhum utilizador encontrado"
        if (row.cells.length > 1) {
            const user = {
                id: parseInt(row.querySelector('.cell-id')?.textContent.trim()) || 0,
                email: row.querySelector('.cell-email')?.textContent.trim() || '',
                username: row.querySelector('.cell-username')?.textContent.trim() || '',
                country: row.querySelector('.cell-country')?.textContent.trim() || '',
                city: row.querySelector('.cell-city')?.textContent.trim() || '',
                created: row.querySelector('.cell-created')?.textContent.trim() || '',
                avatarExt: row.getAttribute('data-avatar-ext') || '',
                bannerExt: row.getAttribute('data-banner-ext') || '',
                genero: row.getAttribute('data-genero') || '',
                role: row.getAttribute('data-role') || '',
                element: row.cloneNode(true)
            };

            allUsers.push(user);
        }
    });

    totalItems = allUsers.length;
    console.log(`Capturados ${totalItems} utilizadores`);
}

// ================================
// EVENT LISTENERS
// ================================
function setupEventListeners() {
    // Toggle do painel de filtros
    const toggleFilters = document.getElementById('toggleUserFilters');
    if (toggleFilters) {
        toggleFilters.addEventListener('click', toggleFiltersPanel);
    }

    // Pesquisa em tempo real
    const searchInput = document.getElementById('searchUserInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Filtros
    const filterOrder = document.getElementById('filterUserOrder');
    const filterRole = document.getElementById('filterRoleUser');
    const filterGenero = document.getElementById('filterGenero');
    const filterAvatarExt = document.getElementById('filterAvatarExt');
    const filterBannerExt = document.getElementById('filterBannerExt');
    const filterCreated = document.getElementById('filterCreated');

    if (filterOrder) filterOrder.addEventListener('change', applyFiltersAndPagination);
    if (filterRole) filterRole.addEventListener('change', applyFiltersAndPagination);
    if (filterGenero) filterGenero.addEventListener('change', applyFiltersAndPagination);
    if (filterAvatarExt) filterAvatarExt.addEventListener('change', applyFiltersAndPagination);
    if (filterBannerExt) filterBannerExt.addEventListener('change', applyFiltersAndPagination);
    if (filterCreated) filterCreated.addEventListener('change', applyFiltersAndPagination);

    // Modais
    setupModalEventListeners();
}

function setupModalEventListeners() {
    // Fechar modais ao clicar no backdrop
    window.addEventListener('click', function (e) {
        if (e.target.classList.contains('uadm-modal-backdrop')) {
            closeAddUserModal();
            closeEditUserModal();
        }
    });

    // Fechar modais com ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAddUserModal();
            closeEditUserModal();
        }
    });
}

// ================================
// FUNÇÕES DE MODAL
// ================================
function openAddUserModal() {
    const modal = document.getElementById('modalAddUser');
    if (modal) {
        modal.classList.add('uadm-modal-show');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Focar no primeiro campo
        const firstInput = modal.querySelector('input[type="email"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeAddUserModal() {
    const modal = document.getElementById('modalAddUser');
    if (modal) {
        modal.classList.remove('uadm-modal-show');
        modal.style.display = 'none';
        document.body.style.overflow = '';

        // Limpar formulário
        const form = modal.querySelector('form');
        if (form) form.reset();
    }
}

function openEditUserModal(id, email, username, name, country, city, genero, role, robloxUsername, emailSocial, whatsapp, youtube, instagram, tiktok, facebook, linkedin, github, twitter) {
    // Preencher campos básicos
    const fields = {
        'edit_user_id': id,
        'edit_email': email,
        'edit_username': username,
        'edit_name': name,
        'edit_country': country,
        'edit_city': city,
        'edit_genero': genero,
        'edit_role': role,
        'edit_roblox': robloxUsername,
        'edit_email_social': emailSocial,
        'edit_whatsapp': whatsapp,
        'edit_youtube': youtube,
        'edit_instagram': instagram,
        'edit_tiktok': tiktok,
        'edit_facebook': facebook,
        'edit_linkedin': linkedin,
        'edit_github': github,
        'edit_twitter': twitter
    };

    Object.entries(fields).forEach(([fieldId, value]) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = value || '';
        }
    });

    // Mostrar modal
    const modal = document.getElementById('modalEditUser');
    if (modal) {
        modal.classList.add('uadm-modal-show');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Focar no primeiro campo
        const firstInput = modal.querySelector('input[type="email"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeEditUserModal() {
    const modal = document.getElementById('modalEditUser');
    if (modal) {
        modal.classList.remove('uadm-modal-show');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ================================
// FILTROS E PESQUISA
// ================================
function toggleFiltersPanel() {
    const panel = document.getElementById('userFilterPanel');
    const button = document.getElementById('toggleUserFilters');

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
    const searchTerm = document.getElementById('searchUserInput')?.value.toLowerCase() || '';
    const order = document.getElementById('filterUserOrder')?.value || 'asc';
    const roleFilter = document.getElementById('filterRoleUser')?.value.toLowerCase() || '';
    const generoFilter = document.getElementById('filterGenero')?.value.toLowerCase() || '';
    const avatarExtFilter = document.getElementById('filterAvatarExt')?.value.toLowerCase() || '';
    const bannerExtFilter = document.getElementById('filterBannerExt')?.value.toLowerCase() || '';
    const createdFilter = document.getElementById('filterCreated')?.value || 'new';

    // Filtrar utilizadores
    filteredUsers = allUsers.filter(user => {
        // Pesquisa por ID, Email, Username, País ou Cidade
        const matchesSearch = user.id.toString().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm) ||
            user.username.toLowerCase().includes(searchTerm) ||
            user.country.toLowerCase().includes(searchTerm) ||
            user.city.toLowerCase().includes(searchTerm);

        // Filtros específicos
        const matchesRole = roleFilter === '' || user.role === roleFilter;
        const matchesGenero = generoFilter === '' || user.genero === generoFilter;
        const matchesAvatarExt = avatarExtFilter === '' || user.avatarExt === avatarExtFilter;
        const matchesBannerExt = bannerExtFilter === '' || user.bannerExt === bannerExtFilter;

        return matchesSearch && matchesRole && matchesGenero && matchesAvatarExt && matchesBannerExt;
    });

    // Ordenar utilizadores
    filteredUsers.sort((a, b) => {
        if (createdFilter === 'old') {
            const dateA = new Date(a.created);
            const dateB = new Date(b.created);
            return dateA - dateB;
        }
        // Ordenar por ID
        return order === 'asc' ? a.id - b.id : b.id - a.id;
    });

    filteredItems = filteredUsers.length;

    // Resetar para primeira página
    currentPage = 1;

    // Aplicar paginação
    updateTableDisplay();
    updatePagination();
    updateStats();

    console.log(`Filtros aplicados: ${filteredItems} utilizadores encontrados`);
}

// ================================
// PAGINAÇÃO
// ================================
function updateTableDisplay() {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) return;

    // Limpar tabela
    tbody.innerHTML = '';

    // Verificar se há utilizadores filtrados
    if (filteredUsers.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="14" class="uadm-text-center" style="padding: 2rem; color: var(--color-gray-400);">
                <i class="bi bi-search" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                Nenhum utilizador encontrado com os critérios selecionados.
            </td>
        `;
        tbody.appendChild(emptyRow);
        return;
    }

    // Calcular índices da página atual
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredUsers.length);

    // Mostrar utilizadores da página atual
    for (let i = startIndex; i < endIndex; i++) {
        const user = filteredUsers[i];
        tbody.appendChild(user.element.cloneNode(true));
    }

    // Reconfigurar event listeners dos botões de ação
    setupTableActionListeners();
}

function setupTableActionListeners() {
    // Melhorar botões de eliminar
    const deleteButtons = document.querySelectorAll('a[href*="delete_id"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const userEmail = this.closest('tr').querySelector('.cell-email')?.textContent.trim();
            const confirmMessage = `Tem certeza que deseja eliminar o utilizador "${userEmail}"?\n\nEsta ação não pode ser desfeita.`;

            if (confirm(confirmMessage)) {
                // Adicionar loading
                button.innerHTML = '<div class="uadm-loading"></div>';
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
    let paginationContainer = document.querySelector('.uadm-pagination');

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
    const tableContainer = document.querySelector('.uadm-table-container');
    if (tableContainer) {
        tableContainer.insertAdjacentElement('afterend', pagination);
    }
}

function createPaginationElement(totalPages) {
    const pagination = document.createElement('div');
    pagination.className = 'uadm-pagination';

    let paginationHTML = '';

    // Botão "Anterior"
    const prevDisabled = currentPage === 1 ? 'disabled' : '';
    paginationHTML += `
        <button class="uadm-pagination-btn" onclick="changePage(${currentPage - 1})" ${prevDisabled}>
            <i class="bi bi-chevron-left"></i>
        </button>
    `;

    // Números das páginas
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    // Primeira página (se não estiver no range)
    if (startPage > 1) {
        paginationHTML += `<button class="uadm-pagination-btn" onclick="changePage(1)">1</button>`;
        if (startPage > 2) {
            paginationHTML += `<span class="uadm-pagination-btn" style="border: none; background: transparent; cursor: default;">...</span>`;
        }
    }

    // Páginas do range atual
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentPage ? 'active' : '';
        paginationHTML += `
            <button class="uadm-pagination-btn ${activeClass}" onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }

    // Última página (se não estiver no range)
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<span class="uadm-pagination-btn" style="border: none; background: transparent; cursor: default;">...</span>`;
        }
        paginationHTML += `<button class="uadm-pagination-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
    }

    // Botão "Próximo"
    const nextDisabled = currentPage === totalPages ? 'disabled' : '';
    paginationHTML += `
        <button class="uadm-pagination-btn" onclick="changePage(${currentPage + 1})" ${nextDisabled}>
            <i class="bi bi-chevron-right"></i>
        </button>
    `;

    // Informações da paginação
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, filteredItems);

    paginationHTML += `
        <div class="uadm-pagination-info">
            Mostrando ${startItem}-${endItem} de ${filteredItems} utilizadores
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
    const tableContainer = document.querySelector('.uadm-table-container');
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
    // Atualizar contador de utilizadores filtrados
    const filteredCountElement = document.getElementById('filteredCount');
    if (filteredCountElement) {
        filteredCountElement.textContent = filteredUsers.length;
    }
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
    if (form.closest('.uadm-modal-content')) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.innerHTML = '<div class="uadm-loading"></div> A processar...';
            submitButton.disabled = true;
        }
    }
});

// Melhorar feedback visual dos botões
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('uadm-btn')) {
        e.target.style.transform = 'scale(0.98)';
        setTimeout(() => {
            e.target.style.transform = '';
        }, 150);
    }
});

// Auto-hide de mensagens de feedback
document.addEventListener('DOMContentLoaded', function () {
    const feedbackMessages = document.querySelectorAll('.uadm-feedback-success, .uadm-feedback-error');
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

console.log('Sistema de gestão de utilizadores carregado com sucesso');