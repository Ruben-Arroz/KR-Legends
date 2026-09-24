/**
 * Geral_cl.js
 * JavaScript comum para todas as páginas (Login e Cadastro)
 * Corrige o carregamento dos logotipos usando caminho absoluto.
 */

const THEME_KEY = 'kr-legends-theme';

document.addEventListener('DOMContentLoaded', function () {
    const savedTheme = localStorage.getItem(THEME_KEY);

    if (savedTheme) {
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    } else {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDark) {
            document.body.classList.add('dark-mode');
            localStorage.setItem(THEME_KEY, 'dark');
        } else {
            localStorage.setItem(THEME_KEY, 'light');
        }
    }

    updateLogoBasedOnTheme();
    initParticles();
});

function toggleTheme() {
    const body = document.body;

    if (body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        localStorage.setItem(THEME_KEY, 'light');
    } else {
        body.classList.add('dark-mode');
        localStorage.setItem(THEME_KEY, 'dark');
    }

    updateLogoBasedOnTheme();
    initParticles();
}

function updateLogoBasedOnTheme() {
    const logo = document.getElementById('brandLogo');
    if (!logo) return;

    // Nome exato dos ficheiros na pasta /KRLegends/Login-Cadastro/Logotipos/
    const darkFile = 'logo-kr-legends-soft-trans-removebg.png';
    const lightFile = 'logo-kr-legends-black-trans-removebg.png';

    // CAMINHO ABSOLUTO: /~a29621/KRLegends/Login-Cadastro/Logotipos/...
    const baseURL = '/~a29621/KRLegends/Login-Cadastro/Logotipos/';
    const filename = document.body.classList.contains('dark-mode') ? darkFile : lightFile;

    // Atribui o src completo ao <img id="brandLogo">
    logo.src = baseURL + filename;

    // Torna a imagem visível
    logo.style.display = 'block';
    logo.style.visibility = 'visible';
    logo.style.opacity = '1';
}

// Inicializa fundo de partículas (opcional)
function initParticles() {
    if (!window.particlesJS) return;

    const isDark = document.body.classList.contains('dark-mode');

    particlesJS('particles-js', {
        particles: {
            number: { value: 50, density: { enable: true, value_area: 800 } },
            color: { value: isDark ? '#ffffff' : '#000000' },
            shape: { type: 'circle', stroke: { width: 0, color: '#000000' } },
            opacity: { value: isDark ? 0.1 : 0.05, random: true },
            size: { value: 3, random: true },
            line_linked: {
                enable: true,
                distance: 150,
                color: isDark ? '#ffffff' : '#000000',
                opacity: isDark ? 0.1 : 0.05,
                width: 1
            },
            move: {
                enable: true,
                speed: 1,
                direction: 'none',
                random: true,
                straight: false,
                out_mode: 'out',
                bounce: false
            }
        },
        interactivity: {
            detect_on: 'canvas',
            events: {
                onhover: { enable: true, mode: 'grab' },
                onclick: { enable: true, mode: 'push' },
                resize: true
            },
            modes: {
                grab: { distance: 140, line_linked: { opacity: 0.3 } },
                push: { particles_nb: 3 }
            }
        },
        retina_detect: true
    });
}

// Toggle de visibilidade da senha
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.password-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const input = this.parentElement.querySelector('input[type="password"], input[type="text"]');
            const icon = this.querySelector('i');

            if (!input) return;

            if (input.getAttribute('type') === 'password') {
                input.setAttribute('type', 'text');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.setAttribute('type', 'password');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});

// Validação de formulários Bootstrap
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
