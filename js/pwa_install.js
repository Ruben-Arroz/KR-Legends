/**
 * KR Legends PWA Install Handler (robusto)
 */
(function () {
    'use strict';

    // Elementos DOM (serão buscados no init)
    let installBtn = null;
    let fallbackText = null;
    let stepsSection = null;

    // Estado
    let deferredPrompt = null;

    function isIOSSafari() {
        const ua = window.navigator.userAgent;
        const iOS = /iPad|iPhone|iPod/.test(ua);
        const webkit = /WebKit/.test(ua);
        const crios = /CriOS/.test(ua);
        return iOS && webkit && !crios;
    }

    function isStandalone() {
        return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
               window.navigator.standalone === true;
    }

    function showInstallButton() {
        if (installBtn) installBtn.style.display = 'inline-flex';
        if (fallbackText) fallbackText.style.display = 'none';
    }

    function hideInstallButton() {
        if (installBtn) installBtn.style.display = 'none';
    }

    function showIOSInstructions() {
        if (fallbackText) {
            fallbackText.innerHTML = `
                <i class="bi bi-info-circle"></i>
                Para instalar: toca em <i class="bi bi-box-arrow-up"></i> e depois em <strong>"Adicionar ao Ecrã Principal"</strong>
            `;
            fallbackText.style.display = 'flex';
        }
        if (stepsSection) stepsSection.style.display = 'block';
        hideInstallButton();
    }

    function showAlreadyInstalled() {
        if (fallbackText) {
            fallbackText.innerHTML = `
                <i class="bi bi-check-circle-fill" style="color: var(--pwa-yellow);"></i>
                <strong>&nbsp;App já instalada! </strong> Abre-a a partir do teu ecrã principal.
            `;
            fallbackText.style.display = 'flex';
        }
        hideInstallButton();
    }

    function handleBeforeInstallPrompt(e) {
        // Previne mini-infobar
        try { e.preventDefault(); } catch(e){}
        deferredPrompt = e;
        window.deferredPWAInstall = e; // compatibilidade global
        console.log('PWA: beforeinstallprompt capturado');
        showInstallButton();
        // dispatch para outros scripts
        document.dispatchEvent(new CustomEvent('pwa:ready'));
    }

    async function handleInstallClick() {
        if (!deferredPrompt) {
            console.warn('PWA: deferredPrompt não está disponível');
            // fallback: abrir modal de instruções ou mostrar mensagem
            document.dispatchEvent(new CustomEvent('kr_pwa_show_manual_instructions'));
            return;
        }

        try {
            deferredPrompt.prompt();
            const choice = await deferredPrompt.userChoice;
            console.log(`PWA: Utilizador ${choice.outcome === 'accepted' ? 'aceitou' : 'recusou'} a instalação`);
            // limpa prompt guardado
            deferredPrompt = null;
            window.deferredPWAInstall = null;
            hideInstallButton();
        } catch (err) {
            console.warn('Erro ao mostrar prompt:', err);
        }
    }

    function handleAppInstalled(e) {
        console.log('PWA: App instalada com sucesso', e);
        hideInstallButton();
        showAlreadyInstalled();
        // Notifica globalmente
        document.dispatchEvent(new CustomEvent('pwa:installed', { detail: e }));
    }

    // Animate Section: observer robusto + immediate check + timeout fallback
    function animateSection() {
        const section = document.getElementById('kr_pwa_section');
        if (!section) return;

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    section.classList.add('visible');
                    try { obs.unobserve(entry.target); } catch(e){}
                }
            });
        }, { threshold: 0.1 });

        try {
            observer.observe(section);
        } catch (e) {
            // ignore
        }

        // immediate check
        const rect = section.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            section.classList.add('visible');
            try { observer.unobserve(section); } catch(e){}
        }

        // safety net
        setTimeout(() => {
            if (!section.classList.contains('visible')) {
                section.classList.add('visible');
            }
        }, 800);
    }

    function init() {
        installBtn = document.getElementById('kr_pwa_install_btn');
        fallbackText = document.getElementById('kr_pwa_fallback_text');
        stepsSection = document.getElementById('kr_pwa_steps');

        // if already installed
        if (isStandalone()) {
            showAlreadyInstalled();
            animateSection();
            return;
        }

        // iOS fallback
        if (isIOSSafari()) {
            showIOSInstructions();
            animateSection();
            return;
        }

        // if global deferred already set by head helper
        if (window.deferredPWAInstall) {
            deferredPrompt = window.deferredPWAInstall;
            showInstallButton();
        }

        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        window.addEventListener('appinstalled', handleAppInstalled);
        document.addEventListener('pwa:ready', () => {
            if (window.deferredPWAInstall) {
                deferredPrompt = window.deferredPWAInstall;
                showInstallButton();
            }
        });

        if (installBtn) installBtn.addEventListener('click', handleInstallClick);

        animateSection();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();