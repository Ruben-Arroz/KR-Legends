// State Management
let currentErrorLocation = 'website';
let currentRating = null;

// Modal Control Functions
function openFeedbackModal() {
  const overlay = document.getElementById('kr-feedbackModal');
  overlay.classList.add('active');
  overlay.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  resetForm('kr-errorForm');
  hideValidationErrors('kr-errorValidation');
}

function closeFeedbackModal() {
  const overlay = document.getElementById('kr-feedbackModal');
  overlay.classList.remove('active');
  overlay.style.display = 'none';
  document.body.style.overflow = '';
}

function closeFeedbackModal() {
  const overlay = document.querySelector('.feedback-modal-overlay');
  overlay.classList.remove('active');
  overlay.style.display = 'none';
  document.body.style.overflow = '';
}

// Close modals when clicking outside
window.onclick = function (event) {
  const target = event.target;
  if (target.classList.contains('kr-modal-overlay') ||
    target.classList.contains('feedback-modal-overlay')) {
    closeFeedbackModal();
  }
}

// Error Location Selection
function setErrorLocation(location) {
  currentErrorLocation = location;
  document.querySelectorAll('.location-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector(`[data-location="${location}"]`).classList.add('active');
  updateErrorTypeOptions(location);
}

// Rating Selection
function setRating(rating) {
  currentRating = rating;
  document.querySelectorAll('.feedback-rating-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector(`[data-rating="${rating}"]`).classList.add('active');
}

// Handle Error Type Change
function handleErrorTypeChange(select) {
  const otherErrorType = document.getElementById('otherErrorType');
  otherErrorType.classList.toggle('hidden', select.value !== 'outro');
}

// Handle Feedback Type Change
function handleFeedbackTypeChange(select) {
  const otherFeedbackType = document.getElementById('otherFeedbackType');
  otherFeedbackType.classList.toggle('hidden', select.value !== 'outro');
}

function updateErrorTypeOptions(location) {
  const select = document.getElementById('errorTypeSelect');
  let options = `<option value="">Selecione o tipo de erro</option>`;

  if (location === 'website') {
    options += `<option value="responsividade_visual">Responsividade/Visual</option>`;
    options += `<option value="funcionalidade">Funcionalidade</option>`;
    options += `<option value="latencia">Latência</option>`;
    options += `<option value="login_registo">Login ou registo</option>`;
  } else if (location === 'game') {
    options += `<option value="visual_grafico">Visual/Gráfico</option>`;
    options += `<option value="jogabilidade">Jogabilidade</option>`;
    options += `<option value="desempenho">Desempenho</option>`;
    options += `<option value="conexao">Conexão</option>`;
  }

  options += `<option value="outro">Outro</option>`;
  select.innerHTML = options;
}

// Validation Functions
function showValidationErrors(containerId, errors) {
  const container = document.getElementById(containerId);
  container.innerHTML = errors.map(err => `<p>${err}</p>`).join('');
  container.classList.remove('hidden');
}

function hideValidationErrors(containerId) {
  const container = document.getElementById(containerId);
  container.classList.add('hidden');
  container.innerHTML = '';
}

function validateErrorForm(formData) {
  const errors = [];
  if (!formData.get('errorType')) errors.push('Tipo de Erro é obrigatório');
  if (formData.get('errorType') === 'outro' && !formData.get('otherErrorTypeText'))
    errors.push('Especificação do tipo de erro é obrigatória');
  if (!formData.get('problemTitle')) errors.push('Título do Problema é obrigatório');
  if (!formData.get('description')) errors.push('Descrição Detalhada é obrigatória');
  if (!formData.get('frequency')) errors.push('Frequência do Erro é obrigatória');
  if (!formData.get('email')) errors.push('Inserir o e-mail é obrigatório');
  return errors;
}

function validateFeedbackForm(formData) {
  const errors = [];
  if (!formData.get('feedbackType')) errors.push('Tipo de Feedback é obrigatório');
  if (formData.get('feedbackType') === 'outro' && !formData.get('otherFeedbackTypeText'))
    errors.push('Especificação do tipo de feedback é obrigatória');
  if (!currentRating) errors.push('Avaliação Geral é obrigatória');
  if (!formData.get('suggestionTitle')) errors.push('Título da Sugestão é obrigatório');
  if (!formData.get('suggestionDescription'))
    errors.push('Descrição Detalhada é obrigatória');
  return errors;
}

// Form Submission Handlers
function handleErrorSubmit(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  const errors = validateErrorForm(formData);
  if (errors.length) {
    showValidationErrors('kr-errorValidation', errors);
    return;
  }
  // ... envio de dados de erro ...
  closeFeedbackModal();
}

function handleFeedbackSubmit(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  const errors = validateFeedbackForm(formData);
  if (errors.length) {
    showValidationErrors('kr-feedbackValidation', errors);
    return;
  }
  // ... envio de dados de feedback ...
  closeFeedbackModal();
}

// Utility Functions
function resetForm(formId) {
  const form = document.getElementById(formId);
  form.reset();
  if (formId === 'kr-errorForm') {
    setErrorLocation('website');
    document.getElementById('otherErrorType').classList.add('hidden');
  } else if (formId === 'kr-feedbackForm') {
    currentRating = null;
    document.querySelectorAll('.feedback-rating-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('otherFeedbackType').classList.add('hidden');
  }
}

// File upload handlers (mantêm ids originais)
document.getElementById('anexo').addEventListener('change', function () {
  const file = this.files[0];
  const label = document.getElementById('anexoLabel');
  if (file) {
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
      alert('O ficheiro é muito grande. O máximo permitido é 5MB.');
      this.value = '';
      label.innerHTML = 'Anexar ficheiro <span class="feedback-file-hint">(imagens ou PDF)</span>';
      return;
    }
    label.innerHTML = `
      <div class="feedback-upload-group">
        <span class="uploaded-file-name">📎 ${file.name}</span>
        <button type="button" class="feedback-remove-file-btn" onclick="removeAnexo(event)">✖</button>
      </div>
    `;
  } else {
    label.innerHTML = 'Anexar ficheiro <span class="feedback-file-hint">(imagens ou PDF)</span>';
  }
});

function removeAnexo(event) {
  event.stopPropagation();
  const input = document.getElementById('anexo');
  const label = document.getElementById('anexoLabel');
  input.value = '';
  label.innerHTML = 'Anexar ficheiro <span class="feedback-file-hint">(imagens ou PDF)</span>';
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  setErrorLocation('website');
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeFeedbackModal();
      closeFeedbackModal();
    }
  });
});