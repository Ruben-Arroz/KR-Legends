// Accordion.js
// Genérico para páginas Suporte.php e Comunidade.php

/**
 * Função para gerar e injetar o HTML dos accordions
 * @param {string} containerId - ID do elemento <div> com classe "accordion"
 * @param {Array} faqData - Array de objetos {id, question, answer}
 *        Onde answer pode ser:
 *          - string: renderiza <p>
 *          - array de strings: renderiza <ul><li>...
 *          - objeto { intro: string, points: string[] }: <p>intro</p><ul>points</ul>
 */
function renderFaqs(containerId, faqData) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container com ID "${containerId}" não encontrado.`);
        return;
    }

    // Limpa conteúdo existente
    container.innerHTML = '';

    // Itera pelos FAQs
    faqData.forEach(({ id, question, answer }, index) => {
        const showClass = index === 0 ? 'show' : '';
        const collapsedAttr = index === 0 ? '' : 'collapsed';

        // Monta o HTML da resposta
        let answerHTML = '';
        if (typeof answer === 'object' && !Array.isArray(answer) && answer.intro && Array.isArray(answer.points)) {
            // Caso com frase e tópicos
            const items = answer.points.map(item => `<li>${item}</li>`).join('');
            answerHTML = `<p>${answer.intro}</p><ul>${items}</ul>`;
        } else if (Array.isArray(answer)) {
            // Apenas tópicos
            const items = answer.map(item => `<li>${item}</li>`).join('');
            answerHTML = `<ul>${items}</ul>`;
        } else {
            // Texto corrido
            answerHTML = `<p>${answer}</p>`;
        }

        // HTML completo do item
        const itemHTML = `
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button ${collapsedAttr}" type="button" data-bs-toggle="collapse" data-bs-target="#${id}">
                        ${question}
                    </button>
                </h3>
                <div id="${id}" class="accordion-collapse collapse ${showClass}" data-bs-parent="#${containerId}">
                    <div class="accordion-body">
                        ${answerHTML}
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', itemHTML);
    });
}

// Inicializa ao carregar o DOM
document.addEventListener('DOMContentLoaded', () => {
    if (typeof faqs !== 'undefined' && Array.isArray(faqs)) {
        renderFaqs('faqAccordion', faqs);
    } else {
        console.error('Variável "faqs" não encontrada ou inválida.');
    }
});