// js/Gerais/notification_API.js

// 1) Regista um SW (necessário para push mais tarde)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js') // IMPORTANTE: sw.js tem de estar na raiz
        .then(reg => console.log('SW registado:', reg.scope))
        .catch(err => console.error('Erro a registar SW:', err));
}

// 2) Quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    // Se ainda não pedimos permissão, pedimos agora
    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            // Verificamos se já foi mostrada a notificação de boas-vindas
            const jaMostrou = localStorage.getItem('welcome_notification_shown');

            if (!jaMostrou) {
                // Guardamos que já mostramos
                localStorage.setItem('welcome_notification_shown', 'true');

                // Mostramos a notificação após 10 segundos
                setTimeout(() => {
                    new Notification('Bem-vindo!', {
                        body: 'Pronto para explorar um novo mundo de adrenalina? 🏁',
                        icon: 'Imagens/Logotipos/Logo KR Legends - black trans.png'
                    });
                }, 10_000);
            }
        } else {
            console.log('Permissão para notificações:', permission);
        }
    });
});