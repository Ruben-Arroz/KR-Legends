// KR Legends Gallery - Clean and Modern Implementation
// Focused on performance and user experience

let galleryData = [];
let currentFilter = 'all';
let currentModalIndex = 0;
let filteredData = [];
let isLoading = false;

// Initialize gallery when DOM is ready
document.addEventListener('DOMContentLoaded', initGallery);

function initGallery() {
    console.log("Inicializando galeria KR Legends...");

    galleryData = window.galleryData || [];

    if (galleryData.length === 0) {
        showErrorMessage();
        return;
    }

    createGallerySection();
    createGalleryItems();
    setupEventListeners();
    initMasonryLayout();
}

function createGallerySection() {
    const gallerySection = document.createElement('section');
    gallerySection.className = 'galeria-section';
    gallerySection.id = 'galeria';

    gallerySection.innerHTML = `
        <div class="container-fluid">
            <div class="galeria-heading">
                <h2 class="galeria-title">Galeria KR Legends</h2>
                <p class="galeria-subtitle">Explore os carros, pistas e momentos épicos do mundo de corridas do KR Legends</p>
            </div>
            
            <div class="galeria-filters">
                <button class="galeria-filter-btn active" data-filter="all">Todos</button>
                <button class="galeria-filter-btn" data-filter="carros">Carros</button>
                <button class="galeria-filter-btn" data-filter="pistas">Pistas</button>
                <button class="galeria-filter-btn" data-filter="corridas">Corridas</button>
                <button class="galeria-filter-btn" data-filter="eventos">Eventos</button>
            </div>
            
            <div class="galeria-container">
                <div class="galeria-grid"></div>
                
                <div class="galeria-loading">
                    <div class="galeria-spinner"></div>
                </div>
                
                <div class="galeria-empty">
                    <h3>Nenhum resultado encontrado</h3>
                    <p>Tente selecionar outra categoria</p>
                </div>
            </div>
        </div>
        
        <div class="galeria-modal">
            <div class="galeria-modal-content">
                <button class="galeria-modal-close" aria-label="Fechar modal">
                    <i class="bi bi-x"></i>
                </button>
                
                <div class="galeria-modal-image-container">
                    <img src="" alt="" class="galeria-modal-image">
                    <video class="galeria-modal-video" controls style="display: none;">
                        <source src="" type="video/mp4">
                        Seu navegador não suporta vídeos.
                    </video>
                </div>
                
                <div class="galeria-modal-details">
                    <h3 class="galeria-modal-title"></h3>
                    <p class="galeria-modal-desc"></p>
                </div>
                
                <div class="galeria-modal-nav">
                    <button class="galeria-modal-nav-btn galeria-prev-btn" aria-label="Anterior">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="galeria-modal-nav-btn galeria-next-btn" aria-label="Próximo">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    const galeriaContainer = document.getElementById('galeria-container');
    galeriaContainer.appendChild(gallerySection);
}

function createGalleryItems() {
    const galleryGrid = document.querySelector('.galeria-grid');
    galleryGrid.innerHTML = '';

    filteredData = [...galleryData];

    galleryData.forEach((item, index) => {
        const itemElement = document.createElement('div');
        itemElement.className = 'galeria-item';
        itemElement.dataset.index = index;
        itemElement.dataset.category = item.category;

        const isVideo = item.imagePath.includes('.mp4');

        itemElement.innerHTML = `
            <div class="galeria-item-wrapper" tabindex="0" role="button" aria-label="Ver ${item.title}">
                <div class="galeria-item-media">
                    ${isVideo ?
                `<video class="galeria-item-video" muted preload="metadata">
                            <source src="${item.imagePath}" type="video/mp4">
                        </video>
                        <div class="galeria-video-overlay">
                            <i class="bi bi-play-circle"></i>
                        </div>` :
                `<img src="${item.imagePath}" alt="${item.alt}" loading="lazy" class="galeria-item-image">`
            }
                </div>
                <div class="galeria-item-content">
                    <span class="galeria-item-category">${getCategoryName(item.category)}</span>
                    <div class="galeria-item-bottom">
                        <h3 class="galeria-item-title">${item.title}</h3>
                        <p class="galeria-item-desc">${item.description}</p>
                    </div>
                </div>
            </div>
        `;

        galleryGrid.appendChild(itemElement);
    });
}

function initMasonryLayout() {
    if (isLoading) return;
    isLoading = true;

    showLoading(true);

    const galleryGrid = document.querySelector('.galeria-grid');
    const images = galleryGrid.querySelectorAll('img');
    const videos = galleryGrid.querySelectorAll('video');
    const totalMedia = images.length + videos.length;
    let loadedMedia = 0;

    function checkAllMediaLoaded() {
        loadedMedia++;
        if (loadedMedia >= totalMedia || totalMedia === 0) {
            showLoading(false);
            applyMasonryLayout();
            isLoading = false;
        }
    }

    if (totalMedia === 0) {
        checkAllMediaLoaded();
    } else {
        images.forEach(img => {
            if (img.complete) {
                checkAllMediaLoaded();
            } else {
                img.addEventListener('load', checkAllMediaLoaded);
                img.addEventListener('error', checkAllMediaLoaded);
            }
        });

        videos.forEach(video => {
            if (video.readyState >= 1) {
                checkAllMediaLoaded();
            } else {
                video.addEventListener('loadedmetadata', checkAllMediaLoaded);
                video.addEventListener('error', checkAllMediaLoaded);
            }
        });
    }
}

function applyMasonryLayout() {
    const items = document.querySelectorAll('.galeria-item');

    items.forEach((item, index) => {
        setTimeout(() => {
            item.classList.add('fade-in');
        }, index * 50);
    });
}

function showLoading(show) {
    const loadingElement = document.querySelector('.galeria-loading');
    const gridElement = document.querySelector('.galeria-grid');

    if (show) {
        loadingElement.style.display = 'block';
        gridElement.style.opacity = '0.5';
    } else {
        loadingElement.style.display = 'none';
        gridElement.style.opacity = '1';
    }
}

function getCategoryName(category) {
    const categoryNames = {
        'carros': 'Carros',
        'pistas': 'Pistas',
        'corridas': 'Corridas',
        'eventos': 'Eventos'
    };
    return categoryNames[category] || category;
}

function setupEventListeners() {
    // Filter buttons
    document.querySelectorAll('.galeria-filter-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelector('.galeria-filter-btn.active').classList.remove('active');
            button.classList.add('active');

            const filterValue = button.getAttribute('data-filter');
            currentFilter = filterValue;
            applyFilter(filterValue);
        });
    });

    // Gallery items
    document.querySelector('.galeria-grid').addEventListener('click', (e) => {
        const galleryItem = e.target.closest('.galeria-item');
        if (galleryItem) {
            const index = parseInt(galleryItem.dataset.index);
            openModal(index);
        }
    });

    // Keyboard navigation for gallery items
    document.querySelector('.galeria-grid').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            const galleryItem = e.target.closest('.galeria-item');
            if (galleryItem) {
                e.preventDefault();
                const index = parseInt(galleryItem.dataset.index);
                openModal(index);
            }
        }
    });

    // Modal controls
    document.querySelector('.galeria-modal-close').addEventListener('click', closeModal);
    document.querySelector('.galeria-prev-btn').addEventListener('click', () => navigateModal('prev'));
    document.querySelector('.galeria-next-btn').addEventListener('click', () => navigateModal('next'));

    // Close modal on background click
    document.querySelector('.galeria-modal').addEventListener('click', (e) => {
        if (e.target === document.querySelector('.galeria-modal')) {
            closeModal();
        }
    });

    // Keyboard navigation for modal
    document.addEventListener('keydown', (e) => {
        if (document.querySelector('.galeria-modal.active')) {
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') navigateModal('prev');
            if (e.key === 'ArrowRight') navigateModal('next');
        }
    });

    // Window resize
    window.addEventListener('resize', debounce(() => {
        if (!isLoading) {
            applyMasonryLayout();
        }
    }, 250));
}

function applyFilter(filterValue) {
    const items = document.querySelectorAll('.galeria-item');
    const emptyElement = document.querySelector('.galeria-empty');

    if (filterValue === 'all') {
        filteredData = [...galleryData];
    } else {
        filteredData = galleryData.filter(item => item.category === filterValue);
    }

    let visibleCount = 0;

    items.forEach((item) => {
        const category = item.dataset.category;
        const shouldShow = filterValue === 'all' || category === filterValue;

        if (shouldShow) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    emptyElement.style.display = visibleCount === 0 ? 'block' : 'none';
}

function openModal(index) {
    const modal = document.querySelector('.galeria-modal');
    const modalImage = modal.querySelector('.galeria-modal-image');
    const modalVideo = modal.querySelector('.galeria-modal-video');
    const modalTitle = modal.querySelector('.galeria-modal-title');
    const modalDesc = modal.querySelector('.galeria-modal-desc');

    const itemData = galleryData[index];
    currentModalIndex = index;

    const isVideo = itemData.imagePath.includes('.mp4');

    if (isVideo) {
        modalImage.style.display = 'none';
        modalVideo.style.display = 'block';
        modalVideo.src = itemData.imagePath;
    } else {
        modalVideo.style.display = 'none';
        modalImage.style.display = 'block';
        modalImage.src = itemData.imagePath;
        modalImage.alt = itemData.alt;
    }

    modalTitle.textContent = itemData.title;
    modalDesc.textContent = itemData.description;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Hide header
    const header = document.querySelector('header');
    if (header) {
        header.classList.add('header-hidden');
    }

    modal.querySelector('.galeria-modal-close').focus();
}

function closeModal() {
    const modal = document.querySelector('.galeria-modal');
    const modalVideo = modal.querySelector('.galeria-modal-video');

    if (modalVideo.src) {
        modalVideo.pause();
        modalVideo.currentTime = 0;
    }

    modal.classList.remove('active');
    document.body.style.overflow = '';

    // Show header
    const header = document.querySelector('header');
    if (header) {
        header.classList.remove('header-hidden');
    }

    // Return focus
    const openedItem = document.querySelector(`[data-index="${currentModalIndex}"] .galeria-item-wrapper`);
    if (openedItem) {
        openedItem.focus();
    }
}

function navigateModal(direction) {
    const currentFilteredIndices = filteredData.map(item =>
        galleryData.findIndex(galleryItem => galleryItem.id === item.id)
    );

    const currentPosition = currentFilteredIndices.indexOf(currentModalIndex);

    let newPosition;
    if (direction === 'prev') {
        newPosition = (currentPosition - 1 + currentFilteredIndices.length) % currentFilteredIndices.length;
    } else {
        newPosition = (currentPosition + 1) % currentFilteredIndices.length;
    }

    openModal(currentFilteredIndices[newPosition]);
}

function showErrorMessage() {
    const galeriaContainer = document.getElementById('galeria-container');
    if (galeriaContainer) {
        const errorElement = document.createElement('div');
        errorElement.className = 'alert alert-warning text-center my-4';
        errorElement.innerHTML = `
            <h4>Ops! Algo deu errado</h4>
            <p>Não foi possível carregar a galeria. Tente novamente mais tarde.</p>
            <button class="btn btn-warning mt-3" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-2"></i>Tentar Novamente
            </button>
        `;
        galeriaContainer.appendChild(errorElement);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Preload images for better performance
setTimeout(() => {
    galleryData.forEach(item => {
        if (!item.imagePath.includes('.mp4')) {
            const img = new Image();
            img.src = item.imagePath;
        }
    });
}, 1000);