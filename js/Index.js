document.addEventListener('DOMContentLoaded', function () {
    const contentContainer = document.getElementById('dynamicContent');

    if (!contentContainer) {
        console.error('Content container not found!');
        return;
    }

    fetch('JSON/Infos/index-data.json')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            renderContentSections(data, contentContainer);
            initIntersectionObserver();
        })
        .catch(error => {
            console.error('Error loading content data:', error);
            contentContainer.innerHTML = `<div class="alert alert-danger">Failed to load content. Please try again later.</div>`;
        });
});

function renderContentSections(data, container) {
    container.innerHTML = '';

    data.sections.forEach(section => {
        let sectionHTML = '';

        switch (section.type) {
            case 'origin':
                sectionHTML = renderOriginSection(section);
                break;
            case 'concept':
                sectionHTML = renderConceptSection(section);
                break;
            case 'features':
                sectionHTML = renderFeaturesSection(section);
                break;
            case 'audience':
                sectionHTML = renderAudienceSection(section);
                break;
            case 'potential':
                sectionHTML = renderPotentialSection(section);
                break;
            case 'inspirations':
                sectionHTML = renderInspirationsSection(section);
                break;
        }

        if (sectionHTML) {
            container.innerHTML += sectionHTML;
        }
    });
}

function renderOriginSection(section) {
    return `
    <div class="section origin-section" id="${section.id}-section">
      <div class="origin-content">
        <h2 class="section-title">${section.title}</h2>
        <div class="content">${section.content}</div>
      </div>
      <img src="${section.illustration}" alt="KR Legends Origin" class="origin-illustration">
    </div>
  `;
}

function renderConceptSection(section) {
    return `
    <div class="section concept-section" id="${section.id}-section">
      <div class="concept-bg" style="background-image: url('${section.background}')"></div>
      <div class="concept-content">
        <h2 class="section-title">${section.title}</h2>
        <div class="content">${section.content}</div>
      </div>
    </div>
  `;
}

function renderFeaturesSection(section) {
    const cardsHTML = section.cards.map(card => `
    <div class="feature-card">
      <div class="feature-icon">
        <i class="${card.icon}"></i>
      </div>
      <h3>${card.title}</h3>
      <p>${card.content}</p>
    </div>
  `).join('');

    return `
    <div class="section features-section" id="${section.id}-section">
      <h2 class="section-title text-center">${section.title}</h2>
      <div class="features-grid">
        ${cardsHTML}
      </div>
    </div>
  `;
}

function renderAudienceSection(section) {
    return `
    <section class="section audience-section" id="${section.id}-section">
  <h2 class="section-title text-center">${section.title}</h2>
  <div class="audience-columns">
    <div class="audience-box audience-icon-box">
      <i class="${section.icon} audience-icon"></i>
    </div>
    <div class="audience-box audience-text-box">
      ${section.content}
    </div>
  </div>
</section>

  `;
}

function renderPotentialSection(section) {
    const cardsHTML = section.cards.map(card => `
    <div class="potential-card">
      <div class="potential-icon">
        <i class="${card.icon}"></i>
      </div>
      <h3>${card.title}</h3>
      <p>${card.content}</p>
    </div>
  `).join('');

    return `
    <div class="section potential-section" id="${section.id}-section">
      <h2 class="section-title">${section.title}</h2>
      <div class="potential-grid">
        ${cardsHTML}
      </div>
    </div>
  `;
}

function renderInspirationsSection(section) {
    const itemsHTML = section.items.map(item => `
    <a href="${item.link}" target="_blank" class="inspiration-item">
      <img src="${item.image}" alt="${item.name}" class="inspiration-img">
      <div class="inspiration-overlay">
        <h4 class="inspiration-name">${item.name}</h4>
      </div>
    </a>
  `).join('');

    return `
    <div class="section inspirations-section" id="${section.id}-section">
      <h2 class="section-title text-center">${section.title}</h2>
      <div class="inspirations-grid">
        ${itemsHTML}
      </div>
    </div>
  `;
}

function initIntersectionObserver() {
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');

                if (entry.target.classList.contains('concept-section')) {
                    entry.target.querySelector('.concept-content').classList.add('visible');
                }

                observer.unobserve(entry.target);
            }
        });
    }, options);

    document.querySelectorAll('.section').forEach(section => {
        observer.observe(section);
    });
}