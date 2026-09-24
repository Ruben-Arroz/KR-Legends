// ================================
// GALERIA ADMIN - JAVASCRIPT
// ================================

let deleteItemId = null;

document.addEventListener('DOMContentLoaded', function () {
    initializeGalleryAdmin();
});

function initializeGalleryAdmin() {
    // Inicializar componentes
    initializeFileUpload();
    initializeFilters();
    initializeModals();
    initializeForms();
    initializeDeleteModal();

    console.log('Gallery Admin initialized');
}

// ================================
// UPLOAD DE FICHEIROS
// ================================
function initializeFileUpload() {
    const addFileInput = document.getElementById('gl-addFile');
    const editFileInput = document.getElementById('gl-editFile');

    if (addFileInput) {
        addFileInput.addEventListener('change', function (e) {
            handleFileSelection(e.target, 'gl-addFileInfo');
        });
    }

    if (editFileInput) {
        editFileInput.addEventListener('change', function (e) {
            handleFileSelection(e.target, 'gl-editFileInfo');
        });
    }
}

function handleFileSelection(input, infoElementId) {
    const infoElement = document.getElementById(infoElementId);
    const file = input.files[0];

    if (!file) {
        infoElement.classList.remove('show', 'success', 'error');
        return;
    }

    // Verificar tamanho do ficheiro (35MB = 35 * 1024 * 1024 bytes)
    const maxSize = 35 * 1024 * 1024;
    const fileSize = file.size;
    const fileSizeFormatted = formatFileSize(fileSize);

    // Verificar tipo de ficheiro
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
    const isValidType = allowedTypes.includes(file.type);

    // Obter extensão do ficheiro
    const fileName = file.name;
    const fileExtension = fileName.split('.').pop().toLowerCase();

    // Validar extensão adicional
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];
    const isValidExtension = allowedExtensions.includes(fileExtension);

    // Obter dimensões se for imagem
    if (file.type.startsWith('image/')) {
        const img = new Image();
        img.onload = function () {
            displayFileInfo(infoElement, file, fileSizeFormatted, fileExtension, maxSize, isValidType && isValidExtension, `${this.width}x${this.height}`);
            URL.revokeObjectURL(img.src);
        };
        img.src = URL.createObjectURL(file);
    } else {
        displayFileInfo(infoElement, file, fileSizeFormatted, fileExtension, maxSize, isValidType && isValidExtension);
    }
}

function displayFileInfo(infoElement, file, fileSizeFormatted, fileExtension, maxSize, isValid, dimensions = null) {
    const isValidSize = file.size <= maxSize;
    const isCompletelyValid = isValidSize && isValid;

    let infoHTML = `
        <div class="file-info-row">
            <strong>Ficheiro:</strong> ${escapeHtml(file.name)}
        </div>
        <div class="file-info-row">
            <strong>Tamanho:</strong> ${fileSizeFormatted}
        </div>
        <div class="file-info-row">
            <strong>Tipo:</strong> ${fileExtension.toUpperCase()}
        </div>
    `;

    if (dimensions) {
        infoHTML += `
            <div class="file-info-row">
                <strong>Dimensões:</strong> ${dimensions}
            </div>
        `;
    }

    if (!isValidSize) {
        infoHTML += `
            <div class="file-info-row error">
                <strong>⚠️ Erro:</strong> O ficheiro excede o limite de 35MB
            </div>
        `;
    }

    if (!isValid) {
        infoHTML += `
            <div class="file-info-row error">
                <strong>⚠️ Erro:</strong> Tipo de ficheiro não suportado
            </div>
        `;
    }

    if (isCompletelyValid) {
        infoHTML += `
            <div class="file-info-row success">
                <strong>✅ Ficheiro válido</strong>
            </div>
        `;
    }

    infoElement.innerHTML = infoHTML;
    infoElement.classList.add('show');
    infoElement.classList.toggle('success', isCompletelyValid);
    infoElement.classList.toggle('error', !isCompletelyValid);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function (m) { return map[m]; });
}

// ================================
// FILTROS
// ================================
function initializeFilters() {
    const toggleFiltersBtn = document.getElementById('gl-toggleFilters');
    const filtersSection = document.getElementById('gl-filtersSection');

    if (toggleFiltersBtn && filtersSection) {
        toggleFiltersBtn.addEventListener('click', function () {
            const isVisible = filtersSection.classList.contains('show');

            if (isVisible) {
                filtersSection.classList.remove('show');
                toggleFiltersBtn.innerHTML = '<i class="bi bi-funnel"></i>Filtros';
            } else {
                filtersSection.classList.add('show');
                toggleFiltersBtn.innerHTML = '<i class="bi bi-funnel-fill"></i>Ocultar Filtros';
            }
        });
    }

    // Verificar se há filtros ativos e mostrar a seção automaticamente
    const urlParams = new URLSearchParams(window.location.search);
    const hasActiveFilters = urlParams.has('category[]') ||
        urlParams.has('is_public') ||
        urlParams.has('extension') ||
        urlParams.has('date_from') ||
        urlParams.has('date_to') ||
        urlParams.get('order') === 'desc';

    if (hasActiveFilters && filtersSection && toggleFiltersBtn) {
        filtersSection.classList.add('show');
        toggleFiltersBtn.innerHTML = '<i class="bi bi-funnel-fill"></i>Ocultar Filtros';
    }
}

function clearFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentSearch = urlParams.get('search');

    // Limpar todos os parâmetros exceto pesquisa
    const newParams = new URLSearchParams();
    if (currentSearch) {
        newParams.set('search', currentSearch);
    }

    window.location.search = newParams.toString();
}

// ================================
// MODAIS
// ================================
function initializeModals() {
    // Limpar formulários quando modais são fechados
    const addModal = document.getElementById('gl-addItemModal');
    const editModal = document.getElementById('gl-editItemModal');

    if (addModal) {
        addModal.addEventListener('hidden.bs.modal', function () {
            resetForm('gl-addItemForm');
        });
    }

    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function () {
            resetForm('gl-editItemForm');
        });
    }
}

function initializeDeleteModal() {
    const confirmDeleteBtn = document.getElementById('gl-confirmDelete');

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            if (deleteItemId) {
                deleteItem(deleteItemId);
                // Fechar modal
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('gl-deleteModal'));
                deleteModal.hide();
                deleteItemId = null;
            }
        });
    }
}

function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();

        // Limpar info dos ficheiros
        const fileInfos = form.querySelectorAll('.gl-file-info');
        fileInfos.forEach(info => {
            info.classList.remove('show', 'success', 'error');
            info.innerHTML = '';
        });
    }
}

// ================================
// FORMULÁRIOS
// ================================
function initializeForms() {
    const addForm = document.getElementById('gl-addItemForm');
    const editForm = document.getElementById('gl-editItemForm');

    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();
            handleAddItem(this);
        });
    }

    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            handleEditItem(this);
        });
    }
}

function validateForm(form, isEdit = false) {
    const errors = [];

    // Validar título
    const title = form.querySelector('[name="title"]').value.trim();
    if (!title) {
        errors.push('O título é obrigatório');
    } else if (title.length > 150) {
        errors.push('O título não pode exceder 150 caracteres');
    }

    // Validar categoria
    const category = form.querySelector('[name="category"]').value;
    if (!category) {
        errors.push('A categoria é obrigatória');
    }

    // Validar descrição
    const description = form.querySelector('[name="description"]').value.trim();
    if (description && description.length > 500) {
        errors.push('A descrição não pode exceder 500 caracteres');
    }

    // Validar texto alternativo
    const alt = form.querySelector('[name="alt"]').value.trim();
    if (alt && alt.length > 255) {
        errors.push('O texto alternativo não pode exceder 255 caracteres');
    }

    // Validar ficheiro (apenas no adicionar)
    if (!isEdit) {
        const fileInput = form.querySelector('[name="file"]');
        if (!fileInput.files[0]) {
            errors.push('Selecione um ficheiro');
        } else {
            const file = fileInput.files[0];
            const maxSize = 35 * 1024 * 1024;

            if (file.size > maxSize) {
                errors.push('O ficheiro excede o limite de 35MB');
            }

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
            if (!allowedTypes.includes(file.type)) {
                errors.push('Tipo de ficheiro não suportado');
            }
        }
    }

    return errors;
}

function handleAddItem(form) {
    // Validar formulário
    const errors = validateForm(form, false);
    if (errors.length > 0) {
        showNotification(errors.join('<br>'), 'error');
        return;
    }

    const formData = new FormData(form);

    // Mostrar loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>A adicionar...';
    submitBtn.disabled = true;

    // Enviar dados
    fetch('gallery-actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Item adicionado com sucesso!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Erro ao adicionar item.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Erro de conexão. Tente novamente.', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

function handleEditItem(form) {
    // Validar formulário
    const errors = validateForm(form, true);
    if (errors.length > 0) {
        showNotification(errors.join('<br>'), 'error');
        return;
    }

    const formData = new FormData(form);

    // Mostrar loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>A guardar...';
    submitBtn.disabled = true;

    // Enviar dados
    fetch('gallery-actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Item atualizado com sucesso!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Erro ao atualizar item.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Erro de conexão. Tente novamente.', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

// ================================
// AÇÕES DOS ITENS
// ================================
function editItem(itemId) {
    // Buscar dados do item
    fetch(`gallery-actions.php?action=get_item&id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.item);
                const editModal = new bootstrap.Modal(document.getElementById('gl-editItemModal'));
                editModal.show();
            } else {
                showNotification('Erro ao carregar dados do item.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Erro de conexão.', 'error');
        });
}

function populateEditForm(item) {
    document.getElementById('gl-editItemId').value = item.GL_ID;
    document.getElementById('gl-editTitle').value = item.GL_TITLE;
    document.getElementById('gl-editDescription').value = item.GL_DESCRIPTION || '';
    document.getElementById('gl-editAlt').value = item.GL_ALT || '';
    document.getElementById('gl-editCategory').value = item.GL_CATEGORY;
    document.getElementById('gl-editIsPublic').checked = item.GL_IS_PUBLIC == 1;

    // Limpar info do ficheiro
    const fileInfo = document.getElementById('gl-editFileInfo');
    fileInfo.classList.remove('show', 'success', 'error');
    fileInfo.innerHTML = '';
}

function confirmDeleteItem(itemId) {
    deleteItemId = itemId;
    const deleteModal = new bootstrap.Modal(document.getElementById('gl-deleteModal'));
    deleteModal.show();
}

function deleteItem(itemId) {
    // Mostrar loading
    showNotification('A eliminar item...', 'info');

    // Enviar pedido de eliminação
    fetch('gallery-actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete&id=${itemId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Item eliminado com sucesso!', 'success');

                // Remover o card da interface
                const itemCard = document.querySelector(`[data-item-id="${itemId}"]`);
                if (itemCard) {
                    itemCard.style.transition = 'all 0.3s ease';
                    itemCard.style.opacity = '0';
                    itemCard.style.transform = 'scale(0.8)';

                    setTimeout(() => {
                        itemCard.remove();

                        // Verificar se não há mais itens
                        const itemsGrid = document.querySelector('.gl-items-grid');
                        if (itemsGrid && itemsGrid.children.length === 0) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    }, 300);
                } else {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                showNotification(data.message || 'Erro ao eliminar item.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Erro de conexão. Tente novamente.', 'error');
        });
}

// ================================
// NOTIFICAÇÕES
// ================================
function showNotification(message, type = 'info') {
    // Remover notificações existentes
    const existingNotifications = document.querySelectorAll('.gl-notification');
    existingNotifications.forEach(notification => notification.remove());

    // Criar nova notificação
    const notification = document.createElement('div');
    notification.className = `gl-notification gl-notification-${type}`;

    const icon = getNotificationIcon(type);
    notification.innerHTML = `
        <div class="gl-notification-content">
            <i class="bi bi-${icon} gl-notification-icon"></i>
            <span class="gl-notification-message">${message}</span>
        </div>
        <button class="gl-notification-close" onclick="this.parentElement.remove()">
            <i class="bi bi-x"></i>
        </button>
    `;

    // Adicionar estilos inline para a notificação
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9999;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-width: 300px;
        max-width: 500px;
        animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Auto-remover após 5 segundos
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle-fill',
        error: 'exclamation-triangle-fill',
        warning: 'exclamation-circle-fill',
        info: 'info-circle-fill'
    };
    return icons[type] || icons.info;
}

function getNotificationColor(type) {
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    return colors[type] || colors.info;
}

// Adicionar estilos de animação
if (!document.getElementById('gl-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'gl-notification-styles';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .gl-notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .gl-notification-icon {
            font-size: 1.25rem;
        }
        
        .gl-notification-message {
            font-weight: 500;
        }
        
        .gl-notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0;
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }
        
        .gl-notification-close:hover {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);
}