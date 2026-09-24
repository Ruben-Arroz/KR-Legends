document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btn-notificacoes');
  const toast = document.getElementById('kr-toast');
  const toastContent = document.getElementById('kr-toast-content');
  const toastClose = document.getElementById('kr-toast-close');

  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const status = Notification.permission;

    // Exibe o status de permissão no console para depuração
    console.log('Permissão atual:', status);

    // Exibir toast de status imediato
    if (status === 'granted') {
      mostrarToast("🔔 Já aceitaste notificações deste site!");
    } else if (status === 'denied') {
      mostrarToast(`
        ⚠️ As notificações foram <strong>recusadas</strong>.<br><br>
        <strong>Como ativar novamente?</strong><br>
        1. Clica no ícone 🔒 à esquerda do endereço do site (barra de URL).<br>
        2. Seleciona <em>“Configurações do site”</em> ou <em>“Permissões”</em>.<br>
        3. Na secção de notificações, escolhe <strong>“Permitir”</strong>.<br>
        4. Recarrega a página após a alteração.
      `, true); // Ativa o layout mais largo
    } else if (status === 'default') {
      // Solicita permissão para notificações
      Notification.requestPermission().then(permission => {
        console.log('Permissão concedida:', permission);  // Verifique o valor de 'permission'
        if (permission === 'granted') {
          // Comente esta linha se necessário para depuração
          // Chama a notificação de boas-vindas de notification_API.js
          // showWelcomeNotification();  // Esta função está definida em notification_API.js

          // Agora, mostramos o toast "Notificações ativadas com sucesso!"
          mostrarToast("🎉 Notificações ativadas com sucesso!");
        } else {
          mostrarToast("❌ Permissão recusada. Para ativar, vai às configurações do navegador.");
        }
      });
    }
  });

  toastClose.addEventListener('click', () => {
    toast.classList.remove('show');
    setTimeout(() => toast.classList.add('hidden'), 400);
  });

  function mostrarToast(mensagem, largo = false) {
    console.log('Tentando mostrar o toast com a mensagem:', mensagem);  // Verifique a mensagem

    toastContent.innerHTML = mensagem;
    toast.classList.remove('hidden');

    // Adiciona a classe para o layout mais largo
    if (largo) {
      toast.classList.add('kr-toast-wide');
    } else {
      toast.classList.remove('kr-toast-wide');
    }

    requestAnimationFrame(() => {
      toast.classList.add('show');
    });

    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.classList.add('hidden'), 1300); // Aumente o tempo aqui para 1500ms
    }, 9500);
  }
});