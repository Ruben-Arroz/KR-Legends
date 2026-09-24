// Settings JavaScript - Sistema de Definições KR Legends
document.addEventListener('DOMContentLoaded', function () {
    // Elementos do DOM
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const passwordMatch = document.getElementById('passwordMatch');
    const submitPasswordBtn = document.getElementById('submitPasswordBtn');
    const passwordForm = document.getElementById('passwordChangeForm');

    // Elementos de requisitos de palavra-passe
    const requirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number'),
        special: document.getElementById('req-special')
    };

    // Função para alternar visibilidade da palavra-passe
    window.togglePassword = function (inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    };

    // Função para validar força da palavra-passe
    function validatePasswordStrength(password) {
        const checks = {
            length: password.length >= 6,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        // Atualizar indicadores visuais dos requisitos
        Object.keys(checks).forEach(key => {
            const element = requirements[key];
            if (element) {
                const icon = element.querySelector('i');
                if (checks[key]) {
                    element.classList.add('met');
                    icon.classList.remove('bi-circle');
                    icon.classList.add('bi-check-circle-fill');
                } else {
                    element.classList.remove('met');
                    icon.classList.remove('bi-check-circle-fill');
                    icon.classList.add('bi-circle');
                }
            }
        });

        // Calcular força
        const strength = Object.values(checks).filter(Boolean).length;

        // Atualizar barra de força
        if (strengthBar && strengthText) {
            strengthBar.className = 'sttgs-strength-fill';

            let strengthClass = '';
            let strengthLabel = '';

            if (password.length === 0) {
                strengthLabel = 'Não definida';
            } else if (strength <= 2) {
                strengthClass = 'strength-weak';
                strengthLabel = 'Fraca';
            } else if (strength <= 4) {
                strengthClass = 'strength-medium';
                strengthLabel = 'Média';
            } else {
                strengthClass = 'strength-strong';
                strengthLabel = 'Forte';
            }

            if (strengthClass) {
                strengthBar.classList.add(strengthClass);
            }

            strengthText.innerHTML = `Força da senha: <span>${strengthLabel}</span>`;
        }

        return {
            strength: strength,
            isValid: strength >= 4 // Requer pelo menos 4 dos 5 critérios
        };
    }

    // Função para verificar se as palavras-passe coincidem
    function checkPasswordMatch() {
        if (!newPasswordInput || !confirmPasswordInput || !passwordMatch) return true;

        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (confirmPassword === '') {
            passwordMatch.textContent = '';
            passwordMatch.className = 'sttgs-password-match';
            return false;
        }

        if (password === confirmPassword) {
            passwordMatch.textContent = '✓ As palavras-passe coincidem';
            passwordMatch.className = 'sttgs-password-match match';
            return true;
        } else {
            passwordMatch.textContent = '✗ As palavras-passe não coincidem';
            passwordMatch.className = 'sttgs-password-match no-match';
            return false;
        }
    }

    // Função para atualizar estado do botão de submissão
    function updateSubmitButton() {
        if (!submitPasswordBtn || !newPasswordInput || !confirmPasswordInput) return;

        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        const passwordValidation = validatePasswordStrength(password);
        const passwordsMatch = checkPasswordMatch();

        const isValid = passwordValidation.isValid &&
            passwordsMatch &&
            password !== '' &&
            confirmPassword !== '';

        submitPasswordBtn.disabled = !isValid;

        if (isValid) {
            submitPasswordBtn.classList.remove('disabled');
        } else {
            submitPasswordBtn.classList.add('disabled');
        }
    }

    // Event listeners para validação de palavra-passe
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function () {
            validatePasswordStrength(this.value);
            updateSubmitButton();
        });

        newPasswordInput.addEventListener('blur', function () {
            validatePasswordStrength(this.value);
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function () {
            checkPasswordMatch();
            updateSubmitButton();
        });

        confirmPasswordInput.addEventListener('blur', function () {
            checkPasswordMatch();
        });
    }

    // Validação de formulários
    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            const passwordValidation = validatePasswordStrength(password);
            const passwordsMatch = checkPasswordMatch();

            if (!passwordValidation.isValid) {
                e.preventDefault();
                showToast('A palavra-passe não cumpre os requisitos de segurança.', 'error');
                return false;
            }

            if (!passwordsMatch) {
                e.preventDefault();
                showToast('As palavras-passe não coincidem.', 'error');
                return false;
            }

            // Adicionar loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processando...';
                submitBtn.classList.add('sttgs-loading');
            }
        });
    }

    // Validação dos modais
    const emailChangeModalForm = document.getElementById('emailChangeModalForm');
    const deleteAccountModalForm = document.getElementById('deleteAccountModalForm');

    if (emailChangeModalForm) {
        emailChangeModalForm.addEventListener('submit', function (e) {
            const currentPassword = document.getElementById('current_password').value;
            const newEmail = document.getElementById('new_email_modal').value;

            if (!currentPassword || !newEmail) {
                e.preventDefault();
                showToast('Preencha todos os campos.', 'error');
                return false;
            }

            // Validar formato do email
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(newEmail)) {
                e.preventDefault();
                showToast('Por favor, insira um email válido.', 'error');
                return false;
            }

            // Adicionar loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processando...';
                submitBtn.classList.add('sttgs-loading');
            }
        });
    }

    if (deleteAccountModalForm) {
        deleteAccountModalForm.addEventListener('submit', function (e) {
            const currentPassword = document.getElementById('current_password_delete').value;
            const confirmCheckbox = document.getElementById('confirmDelete');

            if (!currentPassword) {
                e.preventDefault();
                showToast('Por favor, insira a sua palavra-passe.', 'error');
                return false;
            }

            if (!confirmCheckbox.checked) {
                e.preventDefault();
                showToast('Deve confirmar que compreende as consequências desta ação.', 'error');
                return false;
            }

            // Confirmação adicional
            const confirmed = confirm(
                'ÚLTIMA CONFIRMAÇÃO:\n\n' +
                'Tem a certeza absoluta de que deseja eliminar a sua conta?\n' +
                'Esta ação é IRREVERSÍVEL!'
            );

            if (!confirmed) {
                e.preventDefault();
                return false;
            }

            // Adicionar loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processando...';
                submitBtn.classList.add('sttgs-loading');
            }
        });
    }

    // Animações e efeitos visuais
    function addFadeInAnimation() {
        const cards = document.querySelectorAll('.sttgs-card, .sttgs-sidebar-card');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(20px)';
                    entry.target.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);

                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        cards.forEach(card => {
            observer.observe(card);
        });
    }

    // Inicializar animações se o usuário não preferir movimento reduzido
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        addFadeInAnimation();
    }

    // Função para mostrar notificações toast
    function showToast(message, type = 'info') {
        // Criar elemento toast
        const toast = document.createElement('div');
        toast.className = `sttgs-toast sttgs-toast-${type}`;
        toast.innerHTML = `
            <div class="sttgs-toast-content">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="sttgs-toast-close" onclick="this.parentElement.remove()">
                <i class="bi bi-x"></i>
            </button>
        `;

        // Adicionar estilos se não existirem
        if (!document.querySelector('#toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                .sttgs-toast {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: var(--color-gray-800);
                    color: var(--color-white);
                    padding: 1rem;
                    border-radius: var(--border-radius-md);
                    box-shadow: var(--shadow-lg);
                    border-left: 4px solid;
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    max-width: 400px;
                    animation: slideInRight 0.3s ease;
                }
                
                .sttgs-toast-success { border-left-color: var(--color-success); }
                .sttgs-toast-error { border-left-color: var(--color-danger); }
                .sttgs-toast-info { border-left-color: var(--color-info); }
                .sttgs-toast-warning { border-left-color: var(--color-warning); }
                
                .sttgs-toast-content {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    flex: 1;
                }
                
                .sttgs-toast-close {
                    background: none;
                    border: none;
                    color: var(--color-gray-400);
                    cursor: pointer;
                    padding: 0.25rem;
                    border-radius: var(--border-radius-sm);
                }
                
                .sttgs-toast-close:hover {
                    color: var(--color-white);
                    background: var(--color-gray-700);
                }
                
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
                
                @media (max-width: 575px) {
                    .sttgs-toast {
                        top: 10px;
                        right: 10px;
                        left: 10px;
                        max-width: none;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // Adicionar ao DOM
        document.body.appendChild(toast);

        // Remover automaticamente após 5 segundos
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    // Verificar se há mensagens de status para mostrar como toast
    const statusAlert = document.querySelector('.sttgs-alert');
    if (statusAlert) {
        const message = statusAlert.querySelector('.sttgs-alert-text').textContent;
        let type = 'info';

        if (statusAlert.classList.contains('sttgs-alert-success')) {
            type = 'success';
        } else if (statusAlert.classList.contains('sttgs-alert-error')) {
            type = 'error';
        } else if (statusAlert.classList.contains('sttgs-alert-warning')) {
            type = 'warning';
        }

        // Mostrar toast após um pequeno delay
        setTimeout(() => {
            showToast(message, type);
        }, 500);
    }

    // Função para copiar informações para clipboard
    window.copyToClipboard = function (text, element) {
        navigator.clipboard.writeText(text).then(function () {
            // Feedback visual
            const originalText = element.textContent;
            element.textContent = 'Copiado!';
            element.style.color = 'var(--color-success)';

            setTimeout(() => {
                element.textContent = originalText;
                element.style.color = '';
            }, 2000);

            showToast('Informação copiada para a área de transferência!', 'success');
        }).catch(function () {
            showToast('Erro ao copiar. Tente novamente.', 'error');
        });
    };

    // Adicionar funcionalidade de cópia para email
    const emailElements = document.querySelectorAll('.sttgs-current-email, .sttgs-meta-item');
    emailElements.forEach(element => {
        if (element.textContent.includes('@')) {
            element.style.cursor = 'pointer';
            element.title = 'Clique para copiar';
            element.addEventListener('click', function () {
                const email = this.textContent.match(/[\w.-]+@[\w.-]+\.\w+/);
                if (email) {
                    copyToClipboard(email[0], this);
                }
            });
        }
    });

    // Função para validar força da palavra-passe em tempo real
    function realTimePasswordValidation() {
        if (!newPasswordInput) return;

        let timeout;
        newPasswordInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const validation = validatePasswordStrength(this.value);

                // Adicionar classe visual baseada na força
                this.classList.remove('weak', 'medium', 'strong');
                if (this.value.length > 0) {
                    if (validation.strength <= 2) {
                        this.classList.add('weak');
                    } else if (validation.strength <= 4) {
                        this.classList.add('medium');
                    } else {
                        this.classList.add('strong');
                    }
                }
            }, 300);
        });
    }

    // Inicializar validação em tempo real
    realTimePasswordValidation();

    // Função para detectar caps lock
    function detectCapsLock() {
        const passwordInputs = document.querySelectorAll('input[type="password"]');

        passwordInputs.forEach(input => {
            input.addEventListener('keypress', function (e) {
                const char = String.fromCharCode(e.which);
                const isCapsLock = char.toUpperCase() === char && char.toLowerCase() !== char && !e.shiftKey;

                let capsWarning = this.parentElement.querySelector('.caps-warning');

                if (isCapsLock) {
                    if (!capsWarning) {
                        capsWarning = document.createElement('div');
                        capsWarning.className = 'caps-warning';
                        capsWarning.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Caps Lock está ativo';
                        capsWarning.style.cssText = `
                            color: var(--color-warning);
                            font-size: 0.8rem;
                            margin-top: 0.25rem;
                            display: flex;
                            align-items: center;
                            gap: 0.25rem;
                        `;
                        this.parentElement.appendChild(capsWarning);
                    }
                } else if (capsWarning) {
                    capsWarning.remove();
                }
            });
        });
    }

    // Inicializar detecção de caps lock
    detectCapsLock();

    // Limpar formulários quando modais são fechados
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function () {
            const forms = this.querySelectorAll('form');
            forms.forEach(form => {
                form.reset();
                // Remover estados de loading
                const buttons = form.querySelectorAll('button[type="submit"]');
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('sttgs-loading');
                    // Restaurar texto original do botão
                    const originalText = btn.getAttribute('data-original-text');
                    if (originalText) {
                        btn.innerHTML = originalText;
                    }
                });
            });
        });
    });

    // Salvar texto original dos botões
    document.querySelectorAll('button[type="submit"]').forEach(btn => {
        btn.setAttribute('data-original-text', btn.innerHTML);
    });

    console.log('🎮 KR Legends Settings System initialized successfully!');
});

// Função global para debug (apenas em desenvolvimento)
if (window.location.hostname === 'localhost' || window.location.hostname.includes('dev')) {
    window.KRSettings = {
        version: '2.0.0',
        debug: true,
        testPasswordStrength: function (password) {
            console.log('Testing password strength for:', password.replace(/./g, '*'));
        },
        clearDrafts: function () {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('draft_')) {
                    localStorage.removeItem(key);
                }
            });
            console.log('All drafts cleared');
        }
    };
}