// Obter o ID do produto a partir da URL
const urlParams = new URLSearchParams(window.location.search);
const productId = parseInt(urlParams.get("id")); // ex: produtos.php?id=1

// Procurar o produto com base no ID
const product = products.find(p => p.id === productId);

// Esperar o DOM carregar
document.addEventListener('DOMContentLoaded', () => {
    if (!product) {
        document.querySelector('.main-title').textContent = 'Produto não encontrado';
        document.querySelector('.product-detail').innerHTML = '<p class="text-danger">O produto solicitado não existe.</p>';
        console.error('Produto não encontrado!');
        return;
    }

    initializePage();
});

function showProductDetails(productId) {
    window.location.href = `produtos.php?id=${productId}`;
}


// DOM Elements
const elements = {
    thumbnails: document.querySelector('.thumbnails'),
    mainImage: document.querySelector('.main-image'),
    productModel: document.querySelector('.product-model'),
    productName: document.querySelector('.product-name'),
    productPrice: document.querySelector('.product-price'),
    productDate: document.querySelector('.product-date'),
    stockValue: document.querySelector('.stock-value'),
    colorValue: document.querySelector('.color-value'),
    genderValue: document.querySelector('.gender-value'),
    description: document.querySelector('.product-description p'),
    sizeButtons: document.querySelector('.size-buttons'),
    addToCartBtn: document.querySelector('.add-to-cart-btn'),
    shippingText: document.querySelector('.shipping-text'),
    cartCount: document.querySelector('.cart-count'),
    Info: document.querySelector('.care-instructions'),
};

let selectedSize = null;
let cartItems = 0;

// Initialize the page
function initializePage() {
    renderGallery();
    renderProductInfo();
    renderSizes();
    setupEventListeners();
}

// Render gallery with thumbnails and main image
function renderGallery() {
    // Create thumbnails
    product.image.forEach((image, index) => {
        const button = document.createElement('button');
        button.className = `thumbnail-btn ${index === 0 ? 'active' : ''}`;
        button.innerHTML = `<img src="${image}" alt="${product.name} view ${index + 1}">`;
        button.addEventListener('click', () => updateMainImage(index));
        elements.thumbnails.appendChild(button);
    });

    // Set initial main image
    elements.mainImage.innerHTML = `<img src="${product.image[0]}" alt="${product.name}">`;
}

// Update main image when thumbnail is clicked
function updateMainImage(index) {
    elements.mainImage.querySelector('img').src = product.image[index];
    document.querySelectorAll('.thumbnail-btn').forEach((btn, i) => {
        btn.classList.toggle('active', i === index);
    });
}

// Render product information
function renderProductInfo() {
    elements.productModel.textContent = product.modelName;
    elements.productName.textContent = product.name;
    elements.productPrice.textContent = `€${product.price.toFixed(2)}`;
    elements.productDate.textContent = `Adicionado em ${formatDate(product.dateAdded)}`;
    elements.stockValue.textContent = `${product.stock} unidades`;
    elements.colorValue.textContent = product.color;
    elements.genderValue.textContent = product.gender;
    elements.description.textContent = product.description;
    elements.shippingText.textContent = product.shipping;
    elements.Info.textContent = product.Info;
}

// Render size buttons
function renderSizes() {
    product.sizes.forEach(size => {
        const button = document.createElement('button');
        button.className = 'size-btn';
        button.textContent = size.name;
        button.disabled = !size.available;
        
        if (size.available) {
            button.addEventListener('click', () => selectSize(size.name));
        }
        
        elements.sizeButtons.appendChild(button);
    });
}

// Handle size selection
function selectSize(size) {
    selectedSize = size;
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.classList.toggle('selected', btn.textContent === size);
    });
}

// Format date to local string
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('pt-PT', options);
}

// Handle add to cart
function handleAddToCart() {
    if (!selectedSize) {
        alert('Por favor, selecione um tamanho');
        return;
    }

    cartItems++;
    elements.cartCount.textContent = cartItems;
    
    elements.addToCartBtn.classList.add('success');
    elements.addToCartBtn.textContent = 'Adicionado com sucesso!';
    
    setTimeout(() => {
        elements.addToCartBtn.classList.remove('success');
        elements.addToCartBtn.innerHTML = `
            <span class="cart-icon"></span>
            Adicionar ao carrinho
        `;
    }, 2000);
}