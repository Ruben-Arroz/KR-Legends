// Store.js
// Lógica apenas para lista de produtos, filtros, pesquisa e renderização

// Lista filtrada de produtos
let filteredProducts = [...products];

// Referências aos elementos do DOM
const menuToggle = document.getElementById('menuToggle');
const navContent = document.getElementById('navContent');
const productsGrid = document.getElementById('productsGrid');
const searchInput = document.getElementById('searchInput');
const colorFilter = document.getElementById('colorFilter');
const genderFilter = document.getElementById('genderFilter');
const priceFilter = document.getElementById('priceFilter');
const sortOrder = document.getElementById('sortOrder');
const filterToggle = document.getElementById('filterToggle');
const filtersContainer = document.querySelector('.filters');

// Toggle filtros (apenas na loja)
filterToggle.addEventListener('click', () => {
    filtersContainer.classList.toggle('open');
});

// --------------------------------------------------------------
// 2. EVENT LISTENERS GLOBAIS
//    Menu móvel e inicialização da loja
// --------------------------------------------------------------
// Toggle menu responsivo
menuToggle.addEventListener('click', () => {
    const menuIcon = menuToggle.querySelector('.menu-icon');
    const closeIcon = menuToggle.querySelector('.close-icon');
    menuIcon.classList.toggle('hidden');
    closeIcon.classList.toggle('hidden');
    navContent.classList.toggle('active');
});

// Dispara filtros e pesquisa
searchInput.addEventListener('input', applyFilters);
colorFilter.addEventListener('change', applyFilters);
genderFilter.addEventListener('change', applyFilters);
priceFilter.addEventListener('change', applyFilters);
sortOrder.addEventListener('change', applyFilters);

// Inicializa renderização ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
    renderProducts();
});

// --------------------------------------------------------------
// 3. FILTROS E PESQUISA
//    Aplica pesquisa por texto, cor, género, preço e ordenação
// --------------------------------------------------------------
function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedColor = colorFilter.value;
    const selectedGender = genderFilter.value;
    const selectedPrice = priceFilter.value;
    const selectedSort = sortOrder.value;

    filteredProducts = products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm)
            || product.modelName.toLowerCase().includes(searchTerm);
        const matchesColor = !selectedColor || product.color === selectedColor;
        const matchesGender = !selectedGender || product.gender === selectedGender;
        let matchesPrice = true;
        if (selectedPrice) {
            const [min, max] = selectedPrice.split('-').map(p => p === '+' ? Infinity : Number(p));
            matchesPrice = product.price >= min && (max === Infinity || product.price <= max);
        }
        return matchesSearch && matchesColor && matchesGender && matchesPrice;
    });

    // Ordena de acordo com o critério selecionado
    switch (selectedSort) {
        case 'price-asc': filteredProducts.sort((a, b) => a.price - b.price); break;
        case 'price-desc': filteredProducts.sort((a, b) => b.price - a.price); break;
        case 'newest': filteredProducts.sort((a, b) => new Date(b.dateAdded) - new Date(a.dateAdded)); break;
    }

    renderProducts();
}

// --------------------------------------------------------------
// 4. RENDERIZAÇÃO DE PRODUTOS
//    Criação e inserção dos cartões de produto no DOM
// --------------------------------------------------------------
function createProductCard(product) {
    const imgSrc = Array.isArray(product.image) ? product.image[0] : product.image;
    return `
        <div class="product-card">
            <div class="product-image-container">
                <img src="${imgSrc}" alt="${product.name}" class="product-image">
                <div class="product-model">
                    <h4>${product.modelName}</h4>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-title">${product.name}</h3>
                <p class="product-details">Cor - ${product.color}</p>
                <p class="product-details">Gênero - ${product.gender}</p>
                <p class="product-details">Stock - ${product.stock} unidades</p>
                <p class="product-shipping">${product.shipping}</p>
                <div class="product-price">€${product.price.toFixed(2)}</div>
                <div class="product-buttons">
                    <button class="product-btn product-btn-primary" onclick="addToCart(${product.id});">
                        <i class="bi bi-cart-plus"></i>
                        <span>Adicionar ao carrinho</span>
                    </button>
                    <a href="Produtos.php?id=${product.id}" class="product-btn product-btn-secondary">
                        <i class="bi bi-info-circle"></i>
                        <span>Ver Mais</span>
                    </a>
                </div>
            </div>
        </div>
    `;
}

function renderProducts() {
    productsGrid.innerHTML = filteredProducts.map(createProductCard).join('');
    productsGrid.classList.toggle('few-products', filteredProducts.length <= 2);
}