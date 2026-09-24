//--------------------------------------------------------------
// 1. CONFIGURAÇÃO INICIAL: Variáveis Globais e Constantes
//--------------------------------------------------------------

// Função para pré-carregar imagens (genérica, para qualquer uso necessário)
function preloadImages(images) {
  images.forEach(src => {
    const img = new Image();
    img.src = src;
  });
}

// Array de imagens do carousel do Hero
let currentImageIndex = 0;
const heroBackgrounds = [
  "Imagens/Capas/Capas_sem_logo/capa1_original.jpeg",
  "Imagens/Capas/Capas_sem_logo/Capa3_original.webp",
  "Imagens/Capas/Capas_sem_logo/Capa4_original.webp",
];

//--------------------------------------------------------------
// 2. FUNÇÕES UTILITÁRIAS: Lógicas de Funcionalidade
//--------------------------------------------------------------

// Function to change hero background
function changeHeroBackground(index) {
  const heroSection = document.getElementById('hero');
  heroSection.style.backgroundImage = `url(${heroBackgrounds[index]})`;
}

// Functions for carousel navigation
function nextImage() {
  currentImageIndex = (currentImageIndex + 1) % heroBackgrounds.length;
  changeHeroBackground(currentImageIndex);
}

function prevImage() {
  currentImageIndex = (currentImageIndex - 1 + heroBackgrounds.length) % heroBackgrounds.length;
  changeHeroBackground(currentImageIndex);
}

// Initialize hero background
document.addEventListener('DOMContentLoaded', function () {
  // Set initial hero background
  changeHeroBackground(currentImageIndex);

  // Start auto-rotation for hero background
  setInterval(nextImage, 5000);
});

// Define o idioma atual e atualiza traduções e seletor
function setLanguage(lang) {
  currentLanguage = lang;
  localStorage.setItem('language', lang);
  updateContent();
  updateLanguageSelector();
}

// Atualiza o seletor de idioma com o valor atual
function updateLanguageSelector() {
  const selector = document.getElementById('languageSelector');
  if (selector) {
    selector.value = currentLanguage;
  }
}

// Atualiza os elementos com atributo data-translate conforme a linguagem
function updateContent() {
  const elements = document.querySelectorAll('[data-translate]');
  elements.forEach(element => {
    const keys = element.dataset.translate.split('.');
    let translation = translations[currentLanguage];

    for (const key of keys) {
      if (translation) {
        translation = translation[key];
      }
    }

    if (translation) {
      // Processa marcação markdown para negrito
      translation = translation.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

      if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
        element.placeholder = translation;
      } else {
        element.innerHTML = translation;
      }
    }
  });
}

//--------------------------------------------------------------
// 3. LISTENERS GERAIS: Eventos Globais
//--------------------------------------------------------------

// Fecha o modal ao clicar fora dele (na área com a classe "modal-custom")
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-custom')) {
    closeModal(e.target.id);
  }
});

//--------------------------------------------------------------
// 4. INICIALIZAÇÃO: Configuração ao Carregar o DOM
//--------------------------------------------------------------

// Inicializa o Hero, traduções e smooth scroll após o carregamento da página
document.addEventListener('DOMContentLoaded', () => {


  // Inicializa traduções e atualiza o seletor de idioma
  updateContent();
  updateLanguageSelector();
});

//--------------------------------------------------------------
// 5. LISTENERS ADICIONAIS: Eventos Específicos (Carousel)
//--------------------------------------------------------------

// Reinicia o intervalo de auto-rotacionar as imagens quando um botão do carousel é clicado
document.querySelectorAll('.carousel-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    clearInterval(autoRotateInterval);
    autoRotateInterval = setInterval(nextImage, 5100);
  });
});

// Geral_Store.js

// ——————————————————————————
// 0. Estado Global do Carrinho
// ——————————————————————————
let cart = [];

// ——————————————————————————
// 1. Persistência do Carrinho via localStorage
// ——————————————————————————
const CART_KEY = "kr_cart";
function saveCart() {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}
function loadCart() {
  try {
    const data = localStorage.getItem(CART_KEY);
    cart = data ? JSON.parse(data) : [];
    console.log("» Carrinho carregado do localStorage:", cart);
  } catch {
    cart = [];
  }
}

// ——————————————————————————
// 2. Seletores do Carrinho (modal + contador)
// ——————————————————————————
const miniCart = document.getElementById("miniCart");
const overlay = document.getElementById("overlay");
const openBtn = document.getElementById("openMiniCart");
const closeBtn = document.getElementById("closeMiniCart");
const cartCount = document.getElementById("cartCount");
const miniCartItems = document.getElementById("miniCartItems");
const cartSubtotal = document.getElementById("cartSubtotal");

if (!openBtn || !closeBtn || !miniCart) {
  console.error("⚠️ Geral_Store.js: elementos do carrinho não encontrados.");
}

// ——————————————————————————
// 3. Toggle de Abertura/Fecho
// ——————————————————————————
openBtn.addEventListener("click", () => {
  miniCart.classList.contains("active") ? closeCart() : openCart();
});
closeBtn.addEventListener("click", closeCart);
overlay.addEventListener("click", closeCart);

function openCart() {
  miniCart.classList.add("active");
  overlay.classList.add("active");
  document.body.style.overflow = "hidden";
}
function closeCart() {
  miniCart.classList.remove("active");
  overlay.classList.remove("active");
  document.body.style.overflow = "";
}

// ——————————————————————————
// 4. Contagem e Subtotal
// ——————————————————————————
function updateCartCount() {
  const total = cart.reduce((sum, i) => sum + i.quantity, 0);
  cartCount.textContent = total;
}
function updateCartSubtotal() {
  const total = cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
  cartSubtotal.textContent = `€${total.toFixed(2)}`;
}

// ——————————————————————————
// 5. Render Mini-Cart e Persistência
// ——————————————————————————
function updateMiniCart() {
  miniCartItems.innerHTML = cart.map(item => `
    <div class="mini-cart-item">
      <img src="${item.image[0]}" alt="${item.name}" class="mini-cart-item-image">
      <div class="mini-cart-item-info">
        <h4 class="mini-cart-item-title">${item.name}</h4>
        <p class="mini-cart-item-price">€${item.price.toFixed(2)}</p>
        <div class="mini-cart-item-quantity">
          <button class="quantity-btn"
                  onclick="window._gsUpdateQuantity(${item.id}, ${item.quantity - 1})">-</button>
          <span>${item.quantity}</span>
          <button class="quantity-btn"
                  onclick="window._gsUpdateQuantity(${item.id}, ${item.quantity + 1})"
                  ${item.quantity >= item.stock ? "disabled" : ""}>+</button>
        </div>
      </div>
    </div>
  `).join("");

  updateCartCount();
  updateCartSubtotal();
  saveCart();
}
window._gsUpdateQuantity = updateQuantity;

// ——————————————————————————
// 6. Atualizar Quantidade
// ——————————————————————————
function updateQuantity(productId, newQty) {
  const item = cart.find(i => i.id === productId);
  if (!item) return;
  if (newQty < 1) cart = cart.filter(i => i.id !== productId);
  else item.quantity = Math.min(newQty, item.stock);
  updateMiniCart();
}

// ——————————————————————————
// 7. Adicionar ao Carrinho
// ——————————————————————————
function addToCart(productId, selectedSize = null) {
  const prod = products.find(p => p.id === productId);
  if (!prod) return;

  let existing;
  if (selectedSize !== null) {
    existing = cart.find(i => i.id === productId && i.size === selectedSize)
      || cart.find(i => i.id === productId && i.size === null);
    if (existing && existing.size === null) existing.size = selectedSize;
  } else {
    existing = cart.find(i => i.id === productId && i.size === null);
  }

  if (existing && existing.quantity >= prod.stock) {
    showAlertModal("Produto esgotado", "Este item já não está disponível em stock.");
    return;
  }
  if (existing) existing.quantity++;
  else cart.push({ ...prod, size: selectedSize, quantity: 1 });

  updateMiniCart();
  openCart();
}
window.addToCart = addToCart;

// ——————————————————————————
// 8. Inicialização
// ——————————————————————————
function initCart() {
  if (typeof products === "undefined") {
    console.error("⚠️ Geral_Store.js: variável `products` indefinida.");
    return;
  }
  loadCart();
  updateCartCount();
  updateMiniCart();

  // Override Produtos.js listener
  const btn = document.querySelector(".add-to-cart-btn");
  if (btn) {
    const clone = btn.cloneNode(true);
    btn.parentNode.replaceChild(clone, btn);
    clone.addEventListener("click", () => {
      const id = parseInt(new URLSearchParams(location.search).get("id"), 10);
      const sel = document.querySelector(".size-btn.selected");
      if (!sel) {
        showAlertModal("Tamanho em falta", "Por favor, selecione um tamanho antes de adicionar ao carrinho.");
        return;
      }
      addToCart(id, sel.textContent);
    });
  }
}

document.addEventListener("DOMContentLoaded", initCart);
window.addEventListener("pageshow", initCart);

function showAlertModal(title, message) {
  const modal = document.getElementById('stock-alert-modal');
  const modalTitle = document.getElementById('stock-alert-title');
  const modalMessage = document.getElementById('stock-alert-message');
  const okButton = document.getElementById('stock-alert-ok');

  modalTitle.textContent = title;
  modalMessage.textContent = message;

  modal.style.display = 'flex';
  modal.classList.add('active');

  // Fecha no OK
  okButton.onclick = function () {
    modal.classList.remove('active');
    setTimeout(() => {
      modal.style.display = 'none';
    }, 300); // espera animação
  };
}