/**
 * JavaScript específico para a página de login
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referências aos elementos do formulário
    const loginForm = document.getElementById('loginForm');
    const loginInput = document.getElementById('email');  // Alterado para loginInput
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('emailError');

    // Validação do formulário antes do envio
    loginForm.addEventListener('submit', function (event) {
        const loginValue = loginInput.value.trim();

        // Validação para aceitar email ou nome de usuário
        if (!isValidLogin(loginValue) || !passwordInput.value.trim()) {
            event.preventDefault();

            // Adiciona animação de shake ao formulário quando inválido
            loginForm.classList.add('shake');
            setTimeout(() => {
                loginForm.classList.remove('shake');
            }, 500);
        }
    });

    // Função para validar se o login é um email ou nome de usuário válido
    function isValidLogin(loginValue) {
        // Verifica se é um email válido
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const isEmail = emailRegex.test(loginValue);

        // Verifica se é um nome de usuário válido (apenas letras, números e underscores)
        const usernameRegex = /^[A-Za-z0-9._]{3,30}$/;
        const isUsername = usernameRegex.test(loginValue);

        // O login é válido se for um email ou um nome de usuário válido
        if (isEmail || isUsername) {
            emailError.textContent = '';  // Limpa a mensagem de erro
            return true;
        } else {
            // Exibe mensagem de erro se for inválido
            emailError.textContent = 'O e-mail ou nome de usuário não é válido.';
            return false;
        }
    }

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

    // Botão "Lembrar-me"
    const rememberCheckbox = document.getElementById('remember');

    // Carrega o estado salvo do checkbox "Lembrar-me"
    const savedRemember = localStorage.getItem('kr-legends-remember');
    if (savedRemember === 'true') {
        rememberCheckbox.checked = true;

        // Carrega o login salvo se o checkbox estiver marcado
        const savedLogin = localStorage.getItem('kr-legends-email');
        if (savedLogin) {
            loginInput.value = savedLogin;  // Alterado para loginInput
        }
    }

    // Salva o estado do checkbox "Lembrar-me" e o login quando marcado
    rememberCheckbox.addEventListener('change', function () {
        if (this.checked) {
            localStorage.setItem('kr-legends-remember', 'true');
            if (loginInput.value.trim() !== '') {  // Alterado para loginInput
                localStorage.setItem('kr-legends-email', loginInput.value);  // Salvar login (email ou nome de usuário)
            }
        } else {
            localStorage.setItem('kr-legends-remember', 'false');
            localStorage.removeItem('kr-legends-email');
        }
    });

    // Salva o login quando o formulário é enviado e "Lembrar-me" está marcado
    loginForm.addEventListener('submit', function () {
        if (rememberCheckbox.checked && loginInput.value.trim() !== '') {  // Alterado para loginInput
            localStorage.setItem('kr-legends-email', loginInput.value);  // Salvar login
        }
    });

    // Foco automático no primeiro campo vazio
    if (loginInput.value === '') {  // Alterado para loginInput
        loginInput.focus();
    } else {
        passwordInput.focus();
    }
});