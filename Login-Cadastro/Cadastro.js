/**
 * JavaScript específico para a página de cadastro
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referências aos elementos do formulário
    const registerForm = document.getElementById('registerForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordStrengthText = document.getElementById('passwordStrengthText');

    // Elementos de requisitos de senha
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    // Validação de e-mail personalizada
    emailInput.addEventListener('input', function () {
        validateEmail();
    });

    function validateEmail() {
        const emailValue = emailInput.value.trim();
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (emailValue === '') {
            emailInput.setCustomValidity('O campo e-mail é obrigatório.');
            emailError.textContent = 'O campo e-mail é obrigatório.';
            return false;
        } else if (!emailRegex.test(emailValue)) {
            emailInput.setCustomValidity('Por favor, insira um e-mail válido.');
            emailError.textContent = 'Por favor, insira um e-mail válido.';
            return false;
        } else {
            emailInput.setCustomValidity('');
            emailError.textContent = '';
            return true;
        }
    }



    function validarUsername(username) {
        const regex = /^[A-Za-z0-9._]{3,30}$/; // Somente letras, números e underscores
        const errorElement = document.getElementById('usernameError'); // Adicionar um ID para mostrar o erro

        if (!regex.test(username)) {
            nameInput.setCustomValidity('O nome de usuário não pode conter espaços ou caracteres especiais.');
            errorElement.textContent = "O nome de usuário não pode conter espaços ou caracteres especiais.";
            return false;
        }

        nameInput.setCustomValidity(''); // Limpa o erro
        errorElement.textContent = ''; // Limpa a mensagem de erro
        return true;
    }

    // Evento de validação para o nome de usuário
    nameInput.addEventListener('input', function () {
        validarUsername(nameInput.value);
    });



    

    // Validação de coincidência de senhas
    confirmPasswordInput.addEventListener('input', function () {
        validatePasswordMatch();
    });

    passwordInput.addEventListener('input', function () {
        validatePasswordMatch();
    });

    function validatePasswordMatch() {
        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.setCustomValidity('As senhas não coincidem.');
            passwordError.textContent = 'As senhas não coincidem.';
            return false;
        } else {
            confirmPasswordInput.setCustomValidity('');
            passwordError.textContent = '';
            return true;
        }
    }

    // Medidor de força da senha
    passwordInput.addEventListener('input', function () {
        const password = passwordInput.value;
        let strength = 0;
        let strengthText = '';

        // Limpa classes anteriores
        passwordStrengthBar.className = 'password-strength-bar';

        // Verifica requisitos
        const hasLength = password.length >= 6;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[^A-Za-z0-9]/.test(password);

        // Atualiza indicadores visuais para cada requisito
        updateRequirement(reqLength, hasLength);
        updateRequirement(reqUppercase, hasUppercase);
        updateRequirement(reqLowercase, hasLowercase);
        updateRequirement(reqNumber, hasNumber);
        updateRequirement(reqSpecial, hasSpecial);

        // Adiciona pontos para cada requisito atendido
        if (hasLength) strength++;
        if (hasUppercase) strength++;
        if (hasLowercase) strength++;
        if (hasNumber) strength++;
        if (hasSpecial) strength++;

        // Define a classe e texto do medidor de força baseado na pontuação
        if (password.length === 0) {
            strengthText = 'Não definida';
        } else if (strength <= 2) {
            passwordStrengthBar.classList.add('strength-weak');
            strengthText = 'Fraca';
        } else if (strength <= 4) {
            passwordStrengthBar.classList.add('strength-medium');
            strengthText = 'Média';
        } else {
            passwordStrengthBar.classList.add('strength-strong');
            strengthText = 'Forte';
        }

        // Atualiza o texto do medidor
        passwordStrengthText.innerHTML = `Força da senha: <span>${strengthText}</span>`;
    });

    // Função para atualizar o visual de cada requisito
    function updateRequirement(element, isValid) {
        const iconElement = element.querySelector('.check-icon');

        iconElement.classList.remove('check-valid', 'check-invalid');

        if (isValid) {
            iconElement.classList.add('check-valid');
            iconElement.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else {
            iconElement.classList.add('check-invalid');
            iconElement.innerHTML = '<i class="far fa-circle"></i>';
        }
    }

    // Validação do formulário antes do envio
    registerForm.addEventListener('submit', function (event) {
        let isValid = true;

        // Valida o nome
        if (nameInput.value.trim() === '') {
            nameInput.setCustomValidity('O campo nome é obrigatório.');
            isValid = false;
        } else {
            nameInput.setCustomValidity('');
        }

        // Valida o email
        if (!validateEmail()) {
            isValid = false;
        }

        // Valida a senha e confirmação
        if (passwordInput.value.trim() === '') {
            passwordInput.setCustomValidity('O campo senha é obrigatório.');
            isValid = false;
        } else {
            passwordInput.setCustomValidity('');
        }

        if (!validatePasswordMatch()) {
            isValid = false;
        }

        // Verifica requisitos mínimos de segurança da senha
        const password = passwordInput.value;
        const hasLength = password.length >= 6;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[^A-Za-z0-9]/.test(password);

        if (!hasLength || !hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
            passwordInput.setCustomValidity('A senha não atende aos requisitos mínimos de segurança.');
            isValid = false;
        }

        // Previne o envio do formulário se houver erros
        if (!isValid) {
            event.preventDefault();

            // Adiciona animação de shake ao formulário quando inválido
            registerForm.classList.add('shake');
            setTimeout(() => {
                registerForm.classList.remove('shake');
            }, 500);
        }
    });

    // Animação de shake para o formulário
    const styleSheet = document.createElement('style');
    styleSheet.innerHTML = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
    `;
    document.head.appendChild(styleSheet);

    // Foco automático no primeiro campo
    nameInput.focus();
});