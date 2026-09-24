// JavaScript for PAP Sections Interactivity

// Timeline functionality
document.addEventListener('DOMContentLoaded', function () {
    // Set up timeline marker click handlers
    const timelineMarkers = document.querySelectorAll('.timeline-marker');
    const timelineContent = document.querySelector('.timeline-content');

    if (timelineMarkers.length > 0) {
        // Set the first item as active by default
        timelineMarkers[0].classList.add('active');
        if (timelineContent) {
            updateTimelineContent(timelineMarkers[0].getAttribute('data-phase'));
        }

        // Add click handlers to all markers
        timelineMarkers.forEach(marker => {
            marker.addEventListener('click', function () {
                // Remove active class from all markers
                timelineMarkers.forEach(m => m.classList.remove('active'));

                // Add active class to clicked marker
                this.classList.add('active');

                // Update content
                if (timelineContent) {
                    updateTimelineContent(this.getAttribute('data-phase'));
                }
            });
        });
    }

    // Initialize flip cards
    const flipCards = document.querySelectorAll('.obstacle-card');
    flipCards.forEach(card => {
        card.addEventListener('click', function () {
            this.classList.toggle('flipped');
        });
    });
});

// Update timeline content based on selected phase
function updateTimelineContent(phase) {
    const timelineContent = document.querySelector('.timeline-content');
    if (!timelineContent) return;

    fetch('JSON/Infos/pap-data.json')
        .then(response => response.json())
        .then(data => {
            const phaseData = data.timeline[phase] || data.timeline['planeamento'];

            timelineContent.innerHTML = `
                <div class="timeline-content-inner">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>${phaseData.title}</h4>
                            <div class="timeline-date">${phaseData.date}</div>
                            <p class="timeline-description">${phaseData.description}</p>
                        </div>
                        <div class="col-md-6">
                            <img src="${phaseData.image}" alt="${phaseData.title}" class="img-fluid timeline-image rounded shadow">
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => console.error('Erro ao carregar dados da timeline:', error));
}

// Initialize attachments section with improved tab functionality
document.addEventListener('DOMContentLoaded', function () {
    // Load data from JSON file
    fetch('JSON/Infos/pap-data.json')
        .then(response => response.json())
        .then(data => {
            // Fill mobile accordion
            const mobileAccordion = document.getElementById('mobile-accordion');
            if (mobileAccordion) {
                mobileAccordion.innerHTML = `
                    <div class="custom-accordion-item">
                        <div class="custom-accordion-header">
                            <div class="custom-accordion-icon"><i class="bi bi-file-earmark-slides"></i></div>
                            <span>Apresentações</span>
                            <div class="custom-accordion-toggle"><i class="bi bi-chevron-down"></i></div>
                        </div>
                        <div class="custom-accordion-content">
                            <div class="custom-accordion-body">
                                ${generateAttachmentItems(data.attachments.presentations)}
                            </div>
                        </div>
                    </div>
                    <div class="custom-accordion-item">
                        <div class="custom-accordion-header">
                            <div class="custom-accordion-icon"><i class="bi bi-file-earmark-text"></i></div>
                            <span>Relatórios</span>
                            <div class="custom-accordion-toggle"><i class="bi bi-chevron-down"></i></div>
                        </div>
                        <div class="custom-accordion-content">
                            <div class="custom-accordion-body">
                                ${generateAttachmentItems(data.attachments.reports)}
                            </div>
                        </div>
                    </div>
                    <div class="custom-accordion-item">
                        <div class="custom-accordion-header">
                            <div class="custom-accordion-icon"><i class="bi bi-file-earmark"></i></div>
                            <span>Outros</span>
                            <div class="custom-accordion-toggle"><i class="bi bi-chevron-down"></i></div>
                        </div>
                        <div class="custom-accordion-content">
                            <div class="custom-accordion-body">
                                ${generateAttachmentItems(data.attachments.others)}
                            </div>
                        </div>
                    </div>
                `;

                // Set up accordion functionality with smoother animations
                setupAccordion();
            }

            // Fill desktop tabs content
            const desktopTabs = document.getElementById('desktop-tabs');
            if (desktopTabs) {
                document.getElementById('presentations').innerHTML = generateAttachmentItems(data.attachments.presentations);
                document.getElementById('reports').innerHTML = generateAttachmentItems(data.attachments.reports);
                document.getElementById('others').innerHTML = generateAttachmentItems(data.attachments.others);
            }

            // Enhanced tab switching functionality
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const tabId = this.getAttribute('data-tab');

                    // Deactivate all tabs
                    tabLinks.forEach(link => link.classList.remove('active'));
                    tabPanes.forEach(pane => {
                        pane.classList.remove('active');
                        // Reset the animation
                        pane.style.opacity = '0';
                        pane.style.transform = 'translateY(10px)';
                    });

                    // Activate selected tab
                    this.classList.add('active');
                    const targetPane = document.getElementById(tabId);
                    targetPane.classList.add('active');

                    // Trigger animation after a small delay
                    setTimeout(() => {
                        targetPane.style.opacity = '1';
                        targetPane.style.transform = 'translateY(0)';
                    }, 50);
                });
            });
        })
        .catch(error => console.error('Erro ao carregar o JSON:', error));
});

// Function to generate attachment items (for accordion and tabs)
function generateAttachmentItems(attachments) {
    // Dividir os anexos em duas colunas usando o grid do Bootstrap
    const columns = attachments.map((item, index) => {
        const colClass = 'col-md-6'; // Garante 2 colunas no Bootstrap
        return `
            <div class="${colClass} mb-3">
                <div class="attachment-item">
                    <div class="attachment-icon">
                        <i class="${item.icon}"></i>
                    </div>
                    <div class="attachment-info">
                        <div class="attachment-title"><a href="${item.file}" target="_blank">${item.title}</a></div>
                        <div class="attachment-description">${item.description}</div>
                    </div>
                    <a href="${item.file}" download="${item.file}" class="attachment-link" title="Baixar Anexo">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            </div>
        `;
    }).join('');

    // Envolver os itens gerados com uma div .row para garantir o layout de grid
    return `<div class="row">${columns}</div>`;
}

// Set up accordion functionality with improved animations
function setupAccordion() {
    const accordionItems = document.querySelectorAll('.custom-accordion-item');

    accordionItems.forEach(item => {
        const header = item.querySelector('.custom-accordion-header');
        const content = item.querySelector('.custom-accordion-content');

        header.addEventListener('click', function () {
            // Check if this item is already active
            const isActive = item.classList.contains('active');

            // Close all items first (for accordion behavior)
            accordionItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    const otherContent = otherItem.querySelector('.custom-accordion-content');
                    otherContent.style.maxHeight = null;
                }
            });

            // Toggle the clicked item
            if (!isActive) {
                item.classList.add('active');
                content.style.maxHeight = content.scrollHeight + 'px';
            } else {
                item.classList.remove('active');
                content.style.maxHeight = null;
            }
        });
    });
}