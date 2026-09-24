// Function to create process item card
function createProcessCard(item) {
  return `
      <div class="col-md-4 mb-4">
        <div class="process-card">
          <div class="process-icon">
            <i class="bi ${item.icon}"></i>
          </div>
          <h3 class="process-title">${item.title}</h3>
          <p class="process-description">${item.description}</p>
        </div>
      </div>
    `;
}

// Load and render process items (mantém o JSON original)
fetch('JSON/Infos/Info_Equipa.json')
  .then(response => response.json())
  .then(data => {
    const processItemsContainer = document.getElementById('processItems');
    if (processItemsContainer && data.processItems) {
      processItemsContainer.innerHTML = data.processItems.map(item => createProcessCard(item)).join('');
    }
  })
  .catch(error => console.error('Error loading process items:', error));