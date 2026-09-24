// Function to fetch timeline data
async function fetchTimelineData() {
  try {
    const response = await fetch('JSON/Infos/atualizacoes-data.json');
    const data = await response.json();
    return data.updates;
  } catch (error) {
    console.error('Erro ao obter os dados da timeline:', error);
    return [];
  }
}

let currentPage = 0;
const itemsPerPage = 5;
let timelineData = [];

// Function to create timeline elements
function createTimelineItems(startIndex, count) {
  const timelineContainer = document.getElementById('timeline-container');
  const endIndex = Math.min(startIndex + count, timelineData.length);

  for (let i = startIndex; i < endIndex; i++) {
    const item = timelineData[i];

    const timelineItem = document.createElement('div');
    timelineItem.className = 'timeline-item';

    const timelineDot = document.createElement('div');
    timelineDot.className = 'timeline-dot';

    const timelineDate = document.createElement('div');
    timelineDate.className = 'timeline-date';
    timelineDate.textContent = item.date;

    const timelineContent = document.createElement('div');
    timelineContent.className = 'timeline-content';
    timelineContent.classList.add(i % 2 === 0 ? 'fade-in-right' : 'fade-in-left');

    const timelineImage = document.createElement('img');
    timelineImage.className = 'timeline-image';
    timelineImage.src = item.image;
    timelineImage.alt = item.title;

    const timelineTitle = document.createElement('h3');
    timelineTitle.className = 'timeline-title';
    timelineTitle.textContent = item.title;

    const timelineText = document.createElement('p');
    timelineText.className = 'timeline-text';
    timelineText.textContent = item.text;

    timelineContent.appendChild(timelineImage);
    timelineContent.appendChild(timelineTitle);
    timelineContent.appendChild(timelineText);

    timelineItem.appendChild(timelineDot);
    timelineItem.appendChild(timelineDate);
    timelineItem.appendChild(timelineContent);

    timelineContainer.appendChild(timelineItem);
  }

  // Botão "Carregar mais atualizações"
  const loadMoreBtn = document.getElementById('load-more-btn');
  if (endIndex >= timelineData.length) {
    loadMoreBtn.style.display = 'none';
  } else {
    loadMoreBtn.style.display = 'inline-block';
  }
}

// Scroll animation
function handleScrollAnimations() {
  const timelineItems = document.querySelectorAll('.timeline-content');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = 1;
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  timelineItems.forEach(item => {
    observer.observe(item);
  });
}

// Botão carregar mais
function loadMore() {
  currentPage++;
  createTimelineItems(currentPage * itemsPerPage, itemsPerPage);
  handleScrollAnimations();
}

// Inicialização
document.addEventListener('DOMContentLoaded', async () => {
  timelineData = await fetchTimelineData();
  currentPage = 0; // Reinicia a página
  createTimelineItems(0, itemsPerPage);
  handleScrollAnimations();

  const loadMoreBtn = document.getElementById('load-more-btn');
  loadMoreBtn.addEventListener('click', loadMore);
});
