// Funções para a página de editar perfil

// Atualiza o contador de caracteres da biografia
function updateCharCount() {
    const bio = document.getElementById('bio');
    const count = document.getElementById('char-count');
    if (bio && count) {
        count.textContent = bio.value.length;
    }
}

// Executa quando o documento é carregado
document.addEventListener('DOMContentLoaded', function () {
    // Inicializa o contador de caracteres
    updateCharCount();

    // Valida formato da data de nascimento
    const dateInput = document.getElementById('date_of_birth');
    if (dateInput) {
        dateInput.addEventListener('blur', function () {
            const value = this.value.trim();
            if (value && !isValidDate(value)) {
                this.classList.add('is-invalid');
                if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Por favor, insira uma data válida no formato dd/mm/aaaa.';
                    this.parentNode.insertBefore(feedback, this.nextSibling);
                }
            } else {
                this.classList.remove('is-invalid');
                if (this.nextElementSibling && this.nextElementSibling.classList.contains('invalid-feedback')) {
                    this.nextElementSibling.remove();
                }
            }
        });
    }

    // Valida formato de username
    const usernameInput = document.getElementById('user_name');
    if (usernameInput) {
        usernameInput.addEventListener('blur', function () {
            const value = this.value.trim();
            if (value && !isValidUsername(value)) {
                this.classList.add('is-invalid');
                if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'O nome de usuário só pode conter letras, números e underscores.';
                    this.parentNode.insertBefore(feedback, this.nextSibling);
                }
            } else {
                this.classList.remove('is-invalid');
                if (this.nextElementSibling && this.nextElementSibling.classList.contains('invalid-feedback')) {
                    this.nextElementSibling.remove();
                }
            }
        });
    }

    // Gestão do campo ROBLOX_USERNAME
    const form = document.querySelector('form');
    const robloxInput = document.getElementById('roblox_username');

    if (form && robloxInput) {
        // Cria um campo hidden para controlar o estado original
        const originalInput = document.createElement('input');
        originalInput.type = 'hidden';
        originalInput.name = 'original_roblox_username';
        originalInput.value = robloxInput.value.trim();
        form.appendChild(originalInput);

        // Remove name inicialmente para evitar envio desnecessário
        robloxInput.removeAttribute('name');

        // Monitora mudanças no campo
        robloxInput.addEventListener('input', function () {
            const currentValue = this.value.trim();
            const originalValue = originalInput.value;

            // Adiciona ou remove o name baseado na comparação com valor original
            if (currentValue !== originalValue) {
                this.setAttribute('name', 'ROBLOX_USERNAME');
            } else {
                this.removeAttribute('name');
            }
        });

        // Validação final antes do submit
        form.addEventListener('submit', function (e) {
            const currentValue = robloxInput.value.trim();
            const originalValue = originalInput.value;

            // Se o valor é igual ao original, garante que o campo não será enviado
            if (currentValue === originalValue) {
                robloxInput.removeAttribute('name');
            } else {
                robloxInput.setAttribute('name', 'ROBLOX_USERNAME');
            }
        });
    }

    // Visualização prévia de imagens
    setupImagePreview('avatar', '.perfil-avatar');
    setupImagePreview('banner', '.perfil-banner');
});

// Verifica se uma data está no formato dd/mm/aaaa e é válida
function isValidDate(dateString) {
    if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
        return false;
    }

    const parts = dateString.split('/');
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);

    // Verifica se o mês está entre 1 e 12
    if (month < 1 || month > 12) {
        return false;
    }

    // Verifica o número de dias no mês
    const daysInMonth = new Date(year, month, 0).getDate();
    if (day < 1 || day > daysInMonth) {
        return false;
    }

    // Verifica se a data está no passado
    const today = new Date();
    const inputDate = new Date(year, month - 1, day);
    return inputDate <= today;
}

// Verifica se o username contém apenas letras, números e underscores
function isValidUsername(username) {
    return /^[a-zA-Z0-9_]+$/.test(username);
}

// Configura a visualização prévia de imagens
function setupImagePreview(inputId, previewSelector) {
    const input = document.getElementById(inputId);
    const preview = document.querySelector(previewSelector);

    if (input && preview) {
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
}

const robloxInput = document.getElementById('roblox_username');
if (robloxInput) {
    robloxInput.addEventListener('blur', function () {
        const value = this.value.trim();
        if (value && value !== this.dataset.originalValue) {
            this.classList.add('is-validating');
            const feedback = document.createElement('div');
            feedback.className = 'validating-feedback';
            feedback.textContent = 'Validando username...';
            this.parentNode.appendChild(feedback);
        }
    });
}