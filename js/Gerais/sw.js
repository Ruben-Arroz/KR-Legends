// js/Gerais/sw.js
// (daqui a uns passos podes ouvir 'push' e mostrar notificações mesmo com o site fechado)
self.addEventListener('push', e => {
    const data = e.data?.json() || {};
    self.registration.showNotification(
        data.title || 'Nova notificação',
        { body: data.body || '', icon: data.icon || '/images/icon-notif.png' }
    );
});