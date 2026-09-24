// State Management
let currentErrorLocation = 'website';
let currentRating = null;
let isUserLoggedIn = false; // Will be set by PHP

// Check if user is logged in (this will be set by PHP in the HTML)
function setUserLoginStatus(loggedIn) {
    isUserLoggedIn = loggedIn;
}

// Modal Control Functions
function openReportModal() {
    const overlay = document.getElementById('kr-errorModal');
    overlay.classList.add('active');
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    resetForm('kr-errorForm');
    hideValidationErrors('kr-errorValidation');
    updateErrorTypeOptions(currentErrorLocation);

    // Add smooth entrance animation
    setTimeout(() => {
        overlay.querySelector('.report-modal').style.transform = 'scale(1)';
    }, 10);
}

function closeErrorModal() {
    const overlay = document.getElementById('kr-errorModal');
    const modal = overlay.querySelector('.report-modal');

    // Add exit animation
    modal.style.transform = 'scale(0.95)';
    overlay.style.opacity = '0';

    setTimeout(() => {
        overlay.classList.remove('active');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
        // Reset styles
        modal.style.transform = '';
        overlay.style.opacity = '';
    }, 300);
}

function closeReportModal() {
    const overlay = document.querySelector('.report-modal-overlay');
    if (overlay) {
        const modal = overlay.querySelector('.report-modal');

        // Add exit animation
        if (modal) {
            modal.style.transform = 'scale(0.95)';
        }
        overlay.style.opacity = '0';

        setTimeout(() => {
            overlay.classList.remove('active');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
            // Reset styles
            if (modal) {
                modal.style.transform = '';
            }
            overlay.style.opacity = '';
        }, 300);
    }
}

// Login Prompt Modal Functions
function openLoginPromptModal() {
    const overlay = document.getElementById('kr-loginPromptModal');
    if (overlay) {
        overlay.classList.remove('hidden');
        overlay.classList.add('active');
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Add smooth entrance animation
        setTimeout(() => {
            const modal = overlay.querySelector('.login-prompt-modal');
            if (modal) {
                modal.style.transform = 'scale(1)';
            }
        }, 10);
    }
}

function closeLoginPromptModal() {
    const overlay = document.getElementById('kr-loginPromptModal');
    if (overlay) {
        const modal = overlay.querySelector('.login-prompt-modal');

        // Add exit animation
        if (modal) {
            modal.style.transform = 'scale(0.95)';
        }
        overlay.style.opacity = '0';

        setTimeout(() => {
            overlay.classList.remove('active');
            overlay.classList.add('hidden');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
            // Reset styles
            if (modal) {
                modal.style.transform = '';
            }
            overlay.style.opacity = '';
        }, 300);
    }
}

// Close modals when clicking outside
window.onclick = function (event) {
    const target = event.target;
    if (target.classList.contains('kr-modal-overlay') ||
        target.classList.contains('report-modal-overlay') ||
        target.classList.contains('login-prompt-modal-overlay')) {
        closeErrorModal();
        closeReportModal();
        closeLoginPromptModal();
    }
}

// Error Location Selection
function setErrorLocation(location) {
    currentErrorLocation = location;

    // Update hidden input
    const locationInput = document.getElementById('locationInput');
    if (locationInput) {
        locationInput.value = location;
    }

    // Update button classes with smooth transition
    document.querySelectorAll('.report-location-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.transform = 'scale(1)';
    });

    const activeBtn = document.querySelector(`.report-location-btn[data-location="${location}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
        activeBtn.style.transform = 'scale(1.02)';
        setTimeout(() => {
            activeBtn.style.transform = 'scale(1)';
        }, 150);
    }

    // Update error type options
    updateErrorTypeOptions(location);
}

// Rating Selection
function setRating(rating) {
    currentRating = rating;
    document.querySelectorAll('.report-rating-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.querySelector(`[data-rating="${rating}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
}

// Handle Error Type Change
function handleErrorTypeChange(select) {
    const otherErrorType = document.getElementById('otherErrorType');
    const contactMethodField = document.getElementById('contactMethodField');

    if (otherErrorType) {
        if (select.value === 'outro') {
            otherErrorType.classList.remove('hidden');
            otherErrorType.style.animation = 'slideDown 0.3s ease-out';
        } else {
            otherErrorType.classList.add('hidden');
        }
    }

    // Show/hide contact method field for login/register issues
    if (contactMethodField) {
        if (select.value === 'login_registo') {
            contactMethodField.classList.remove('hidden');
            contactMethodField.style.animation = 'slideDown 0.3s ease-out';
        } else {
            contactMethodField.classList.add('hidden');
        }
    }
}

// Handle Feedback Type Change
function handleFeedbackTypeChange(select) {
    const otherFeedbackType = document.getElementById('otherFeedbackType');
    if (otherFeedbackType) {
        if (select.value === 'outro') {
            otherFeedbackType.classList.remove('hidden');
            otherFeedbackType.style.animation = 'slideDown 0.3s ease-out';
        } else {
            otherFeedbackType.classList.add('hidden');
        }
    }
}

// Update Error Types Based on Location
function updateErrorTypeOptions(location) {
    const select = document.getElementById('errorTypeSelect');
    if (!select) return;

    let options = `<option value="">Selecione o tipo de erro</option>`;

    if (location === 'website') {
        options += `<option value="responsividade_visual">Responsividade/Visual</option>`;
        options += `<option value="funcionalidade">Funcionalidade</option>`;
        options += `<option value="latencia">Latência</option>`;
        options += `<option value="login_registo">Login ou registo</option>`;
    } else if (location === 'game') {
        options += `<option value="visual_grafico">Visual/Gráfico</option>`;
        options += `<option value="jogabilidade">Jogabilidade</option>`;
        options += `<option value="desempenho">Desempenho</option>`;
        options += `<option value="conexao">Conexão</option>`;
    }

    options += `<option value="outro">Outro</option>`;
    select.innerHTML = options;

    // Reset dependent fields
    const otherErrorType = document.getElementById('otherErrorType');
    const contactMethodField = document.getElementById('contactMethodField');

    if (otherErrorType) {
        otherErrorType.classList.add('hidden');
    }
    if (contactMethodField) {
        contactMethodField.classList.add('hidden');
    }
}

// Validation Functions
function showValidationErrors(containerId, errors) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = errors.map(err => `<p>${err}</p>`).join('');
        container.classList.remove('hidden');
        container.style.animation = 'slideDown 0.3s ease-out';

        // Scroll to errors
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function hideValidationErrors(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.classList.add('hidden');
        container.innerHTML = '';
    }
}

function validateErrorForm(formData) {
    const errors = [];

    if (!formData.get('errorType')) {
        errors.push('Tipo de Erro é obrigatório');
    }

    if (formData.get('errorType') === 'outro' && !formData.get('otherErrorTypeText')) {
        errors.push('Especificação do tipo de erro é obrigatória');
    }

    if (!formData.get('problemTitle')) {
        errors.push('Título do Problema é obrigatório');
    }

    if (!formData.get('description')) {
        errors.push('Descrição Detalhada é obrigatória');
    }

    if (!formData.get('frequency')) {
        errors.push('Frequência do Erro é obrigatória');
    }

    // Validate contact method if login/register issue is selected
    if (formData.get('errorType') === 'login_registo' && !formData.get('contactMethod')) {
        errors.push('Forma de contacto é obrigatória para problemas de login/registo');
    }

    return errors;
}

// Form Submission Handler for Reporting Errors
function handleErrorSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const errorType = formData.get('errorType');

    // Check if user needs to be logged in - FIXED LOGIC
    // User needs login UNLESS it's a login/register issue OR user is already logged in
    const isLoginRegisterIssue = errorType === 'login_registo';
    const needsLogin = !isUserLoggedIn && !isLoginRegisterIssue;

    if (needsLogin) {
        // Show login prompt modal
        hideValidationErrors('kr-errorValidation');
        closeErrorModal();
        setTimeout(() => {
            openLoginPromptModal();
        }, 300);
        return;
    }

    // Validate form
    const errors = validateErrorForm(formData);

    if (errors.length) {
        showValidationErrors('kr-errorValidation', errors);
        return;
    }

    // Add loading state to submit button
    const submitBtn = event.target.querySelector('.report-submit-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'A enviar...';
    submitBtn.disabled = true;

    // If validation passes, submit the form
    setTimeout(() => {
        event.target.submit();
    }, 500);
}

// Utility Functions
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.reset();

    if (formId === 'kr-errorForm') {
        setErrorLocation('website');

        const otherErrorType = document.getElementById('otherErrorType');
        const contactMethodField = document.getElementById('contactMethodField');

        if (otherErrorType) {
            otherErrorType.classList.add('hidden');
        }
        if (contactMethodField) {
            contactMethodField.classList.add('hidden');
        }
    } else if (formId === 'kr-feedbackForm') {
        currentRating = null;
        document.querySelectorAll('.report-rating-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        const otherFeedbackType = document.getElementById('otherFeedbackType');
        if (otherFeedbackType) {
            otherFeedbackType.classList.add('hidden');
        }
    }
}

// File upload handlers
function initializeFileUpload() {
    const fileInput = document.getElementById('anexo');
    const fileLabel = document.getElementById('anexoLabel');

    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];

            if (file) {
                const maxSize = 25 * 1024 * 1024; // 25 MB
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4'];

                if (file.size > maxSize) {
                    showFileError('O ficheiro é muito grande. O máximo permitido é 25 MB.');
                    this.value = '';
                    resetFileLabel();
                    return;
                }

                if (!allowedTypes.includes(file.type)) {
                    showFileError('Tipo de ficheiro não permitido. Use apenas imagens, PDF ou vídeos MP4.');
                    this.value = '';
                    resetFileLabel();
                    return;
                }

                // Show selected file
                fileLabel.innerHTML = `
                    <div class="report-upload-group">
                        <span class="uploaded-file-name">📎 ${file.name}</span>
                        <button type="button" class="report-remove-file-btn" onclick="removeAnexo(event)">✖</button>
                    </div>
                `;
            } else {
                resetFileLabel();
            }
        });
    }
}

function showFileError(message) {
    // Create temporary error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'file-error-message';
    errorDiv.style.cssText = `
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: rgba(239, 68, 68, 0.1);
        border-radius: 4px;
        border: 1px solid rgba(239, 68, 68, 0.3);
    `;
    errorDiv.textContent = message;

    const fileUpload = document.querySelector('.report-file-upload');
    if (fileUpload) {
        // Remove existing error message
        const existingError = fileUpload.querySelector('.file-error-message');
        if (existingError) {
            existingError.remove();
        }

        fileUpload.appendChild(errorDiv);

        // Remove error message after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
}

function resetFileLabel() {
    const fileLabel = document.getElementById('anexoLabel');
    if (fileLabel) {
        fileLabel.innerHTML = 'Anexar ficheiro <span class="report-file-hint">(imagens, PDF ou vídeo)</span>';
    }
}

function removeAnexo(event) {
    event.stopPropagation();
    const input = document.getElementById('anexo');
    if (input) {
        input.value = '';
        resetFileLabel();
    }
}

// Enhanced keyboard navigation
function setupKeyboardNavigation() {
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeErrorModal();
            closeReportModal();
            closeLoginPromptModal();
        }

        // Tab navigation improvements
        if (e.key === 'Tab') {
            const activeModal = document.querySelector('.report-modal-overlay.active, .login-prompt-modal-overlay.active');
            if (activeModal) {
                const focusableElements = activeModal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];

                if (e.shiftKey && document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                } else if (!e.shiftKey && document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        }
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    setErrorLocation('website');
    initializeFileUpload();
    setupKeyboardNavigation();

    // Set up smooth transitions for form elements
    const formElements = document.querySelectorAll('.report-modal-form input, .report-modal-form select, .report-modal-form textarea');
    formElements.forEach(element => {
        element.addEventListener('focus', function () {
            this.style.transform = 'translateY(-1px)';
        });

        element.addEventListener('blur', function () {
            this.style.transform = 'translateY(0)';
        });
    });

    // Add loading states to buttons
    const buttons = document.querySelectorAll('.report-location-btn, .report-submit-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function () {
            if (!this.disabled) {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            }
        });
    });
});

// Expose functions globally for onclick handlers
window.openReportModal = openReportModal;
window.closeErrorModal = closeErrorModal;
window.closeReportModal = closeReportModal;
window.openLoginPromptModal = openLoginPromptModal;
window.closeLoginPromptModal = closeLoginPromptModal;
window.setErrorLocation = setErrorLocation;
window.setRating = setRating;
window.handleErrorTypeChange = handleErrorTypeChange;
window.handleFeedbackTypeChange = handleFeedbackTypeChange;
window.handleErrorSubmit = handleErrorSubmit;
window.removeAnexo = removeAnexo;
window.setUserLoginStatus = setUserLoginStatus;