// Creditos.js - Funcionalidades para a página de créditos do KR Legends

document.addEventListener('DOMContentLoaded', function () {
    // Carregar dados dos créditos
    fetch('JSON/Infos/creditos-data.json')
        .then(response => response.json())
        .then(data => {
            renderCreditosData(data);
            setupAnimations();
            addRacingStripes();

            // Inicializar os tabs dos desenvolvedores
            initDevTabs();
        })
        .catch(error => {
            console.error('Erro ao carregar os dados de créditos:', error);
            // Fallback em caso de erro
            renderFallbackContent();
        });
});

// Carousel de fundo para o Hero
const heroImages = [
    "Imagens/Capas/Capas_sem_logo/capa1_original.jpeg",
    "Imagens/Capas/Capas_sem_logo/Capa3_original.webp",
    "Imagens/Capas/Capas_sem_logo/Capa4_original.webp",
];

// Adicionar elementos decorativos de corrida
function addRacingStripes() {
    const sections = document.querySelectorAll('.creditos-section');

    sections.forEach(section => {
        const stripe1 = document.createElement('div');
        stripe1.className = 'racing-stripe racing-stripe-1';

        const stripe2 = document.createElement('div');
        stripe2.className = 'racing-stripe racing-stripe-2';

        section.appendChild(stripe1);
        section.appendChild(stripe2);
    });
}

// Renderizar os dados na página
function renderCreditosData(data) {
    const mainContainer = document.getElementById('creditos-container');
    if (!mainContainer) return;

    // Criação da secção de desenvolvedores
    const desenvolvedoresHtml = createSecaoDesenvolvedores(data.desenvolvedores);

    // Criação da secção de instituições
    const instituicoesHtml = createSecaoInstituicoes(data.instituicoes);

    // Criação da secção de orientadores
    const orientadoresHtml = createSecaoOrientadores(data.orientadores);

    // Criação da secção de parceiros
    const parceirosHtml = createSecaoParceiros(data.parceiros);

    // Criação da secção de agradecimentos
    const agradecimentosHtml = createSecaoAgradecimentos(data.agradecimentos);

    // Criação da mensagem final
    const mensagemFinalHtml = createMensagemFinal(data.mensagemFinal);

    // Combinar todas as secções
    mainContainer.innerHTML = desenvolvedoresHtml +
        instituicoesHtml +
        orientadoresHtml +
        parceirosHtml +
        agradecimentosHtml +
        mensagemFinalHtml;

    setTimeout(() => {
        document.querySelectorAll('#agradecimentos .agradecimento-card')
            .forEach(card => card.classList.add('show'));
    }, 100);
}

// Criar a secção de desenvolvedores com sistema de abas
function createSecaoDesenvolvedores(desenvolvedores) {
    if (!desenvolvedores || desenvolvedores.length === 0) return '';

    let html = `
        <section id="desenvolvedores" class="creditos-section">
            <div class="container section-container">
                <h2 class="section-title text-center">Desenvolvedores e Proprietários</h2>
                <div class="row">
                    <div class="col-md-8 offset-md-2 fade-in">
                        <div class="membro-card">
                            <div class="dev-tabs">
    `;

    // Criar as abas para cada desenvolvedor
    desenvolvedores.forEach((dev, index) => {
        // O primeiro desenvolvedor será ativo por padrão
        const activeClass = index === 0 ? 'active' : '';
        html += `
            <button class="dev-tab-btn ${activeClass}" data-dev-id="${dev.id}">${dev.nome}</button>
        `;
    });

    html += `
                            </div>
                            <div class="dev-contents">
    `;

    // Criar o conteúdo para cada desenvolvedor
    desenvolvedores.forEach((dev, index) => {
        // O primeiro desenvolvedor será ativo por padrão
        const activeClass = index === 0 ? 'active' : '';
        html += `
            <div class="dev-content ${activeClass}" data-dev-id="${dev.id}">
                <h3 class="membro-nome">${dev.nome}</h3>
                <p class="membro-titulo">${dev.titulo}</p>
                <p class="membro-descricao">${dev.descricao}</p>
            </div>
        `;
    });

    html += `
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    `;

    return html;
}

// Inicializar o sistema de abas para os desenvolvedores
function initDevTabs() {
    const tabButtons = document.querySelectorAll('.dev-tab-btn');
    if (!tabButtons.length) return;

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Remover a classe ativa de todos os botões
            tabButtons.forEach(btn => btn.classList.remove('active'));
            // Adicionar a classe ativa ao botão clicado
            this.classList.add('active');

            // Obter o ID do desenvolvedor
            const devId = this.getAttribute('data-dev-id');

            // Encontrar todos os conteúdos
            const allContents = document.querySelectorAll('.dev-content');

            // Esconder todos os conteúdos
            allContents.forEach(content => content.classList.remove('active'));

            // Mostrar o conteúdo correspondente ao desenvolvedor
            const activeContent = document.querySelector(`.dev-content[data-dev-id="${devId}"]`);
            if (activeContent) {
                activeContent.classList.add('active');
            }
        });
    });
}

// Criar a secção de instituições
function createSecaoInstituicoes(instituicoes) {
    let html = `
        <section id="instituicoes" class="creditos-section">
            <div class="container section-container">
                <h2 class="section-title text-center">Agradecimentos Institucionais</h2>
                <div class="row">
    `;

    instituicoes.forEach((inst, index) => {
        const animClass = index % 2 === 0 ? 'fade-in' : 'fade-in';
        const delayClass = `delay-${(index % 3) + 1}`;

        html += `
            <div class="col-lg-8 offset-lg-2 ${animClass} ${delayClass}">
                <div class="instituicao-card">
                    <h3 class="instituicao-nome">${inst.nome}</h3>
                    <p class="instituicao-descricao">${inst.descricao}</p>
                </div>
            </div>
        `;
    });

    html += `
                </div>
            </div>
        </section>
    `;

    return html;
}

// Criar a secção de orientadores
function createSecaoOrientadores(orientadores) {
    let html = `
        <section id="orientadores" class="creditos-section">
            <div class="container section-container">
                <h2 class="section-title text-center">Orientação Académica</h2>
                <div class="row justify-content-center">
    `;

    orientadores.forEach((orient, index) => {
        const animClass = 'fade-in';
        const delayClass = `delay-${index + 1}`;

        html += `
            <div class="col-md-4 ${animClass} ${delayClass}">
                <div class="membro-card">
                    <h3 class="membro-nome">${orient.nome}</h3>
                    <p class="membro-titulo">${orient.titulo}</p>
                    <p class="membro-descricao">${orient.descricao}</p>
                </div>
            </div>
        `;
    });

    html += `
                </div>
            </div>
        </section>
    `;

    return html;
}

// Criar a secção de parceiros
function createSecaoParceiros(parceiros) {
    let html = `
        <section id="parceiros" class="creditos-section">
            <div class="container section-container">
                <h2 class="section-title text-center">Parceiros Empresariais</h2>
                <div class="row">
    `;

    parceiros.forEach((parceiro, index) => {
        const isOdd = index % 2 !== 0;
        const colClass = isOdd ? 'col-md-6 offset-md-6' : 'col-md-6';
        const animClass = isOdd ? 'slide-in-right' : 'slide-in-left';
        const delayClass = `delay-${index + 1}`;

        html += `
            <div class="${colClass} ${animClass} ${delayClass}">
                <div class="instituicao-card">
                    <h3 class="instituicao-nome">${parceiro.nome}</h3>
                    <p class="instituicao-descricao">${parceiro.descricao}</p>
                </div>
            </div>
        `;
    });

    html += `
                </div>
            </div>
        </section>
    `;

    return html;
}

// Criar a secção de agradecimentos em grid de cartões
function createSecaoAgradecimentos(agradecimentos) {
    const titulos = [
        'Família e Amigos',
        'Colegas de Turma',
        'Utilizadores-Teste'
    ];

    let html = `
    <section id="agradecimentos" class="creditos-section">
      <div class="container section-container">
        <h2 class="section-title text-center">Agradecimentos Especiais</h2>
        <div class="agradecimentos-grid">
    `;

    agradecimentos.forEach((texto, i) => {
        const delay = i * 150;
        html += `
          <div class="agradecimento-card" style="transition-delay: ${delay}ms;">
            <div class="card-icon">➤</div>
            <h3 class="card-title">${titulos[i] || 'Agradecimento'}</h3>
            <p class="card-text">${texto}</p>
          </div>
        `;
    });

    html += `
        </div>
      </div>
    </section>
    `;
    return html;
}

// Criar a mensagem final
function createMensagemFinal(mensagem) {
    return `
        <section class="mensagem-final">
            <div class="container">
                <p class="mensagem-texto pulse">${mensagem}</p>
            </div>
        </section>
    `;
}

// Configurar animações ao rolar
function setupAnimations() {
    const sectionTitles = document.querySelectorAll('.section-title');
    const titleObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, { threshold: 0.3 });

    sectionTitles.forEach(title => {
        titleObserver.observe(title);
    });

    // Animação para elementos com animações
    const animElements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right');
    const animObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Manter as classes de animação, mas tornar o elemento visível
                entry.target.style.visibility = 'visible';
            } else {
                // Esconder o elemento quando não estiver visível
                if (entry.boundingClientRect.y > 0) {
                    entry.target.style.visibility = 'hidden';
                }
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -100px 0px' });

    animElements.forEach(el => {
        // Inicialmente escondido
        el.style.visibility = 'hidden';
        animObserver.observe(el);
    });

    // Efeito de paralaxe suave nos elementos decorativos
    window.addEventListener('scroll', function () {
        const stripes = document.querySelectorAll('.racing-stripe');
        const scrollPosition = window.scrollY;

        stripes.forEach((stripe, index) => {
            const speed = index % 2 === 0 ? 0.05 : -0.03;
            const yPos = scrollPosition * speed;
            stripe.style.transform = `skewY(${index % 2 === 0 ? '-2deg' : '2deg'}) translateY(${yPos}px)`;
        });
    });
}

// Função de fallback caso o JSON não carregue
function renderFallbackContent() {
    const mainContainer = document.getElementById('creditos-container');
    if (!mainContainer) return;

    mainContainer.innerHTML = `
        <section class="creditos-section">
            <div class="container">
                <h2 class="section-title text-center">Equipa KR Legends</h2>
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="membro-card">
                            <p class="text-center">Não foi possível carregar os dados dos créditos. Por favor, tente novamente mais tarde.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    `;
}