// 1. Scroll Effect for Navbar
window.addEventListener('scroll', function () {
  const navbar = document.getElementById('navbar');
  if (navbar) {
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  }
});

// 2. Responsive Hamburger Menu
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');

if (mobileMenuBtn && navMenu) {
  mobileMenuBtn.addEventListener('click', function () {
    navMenu.classList.toggle('active');
    const icon = mobileMenuBtn.querySelector('i');
    if (icon) {
      if (navMenu.classList.contains('active')) {
        icon.classList.remove('bi-list');
        icon.classList.add('bi-x-lg');
      } else {
        icon.classList.remove('bi-x-lg');
        icon.classList.add('bi-list');
      }
    }
  });
}

// 3. Highlight Active Navigation Item based on current URL
(function highlightActiveNavLink() {
  const currentPath = window.location.pathname.split('/').pop() || 'welcome.php';
  const navLinks = document.querySelectorAll('.nav-link-item');

  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (!href) return;

    const baseCurrent = currentPath.toLowerCase().replace('.html', '').replace('.php', '');
    const baseHref = href.toLowerCase().replace('.html', '').replace('.php', '');

    if (baseCurrent === baseHref || (baseCurrent === '' && baseHref === 'welcome') || (baseCurrent === 'index' && baseHref === 'welcome')) {
      link.classList.add('active');
    }
  });
})();

// 4. Search and Filter Logic for Ordinances
const searchInput = document.getElementById('searchInput');
const categorySelect = document.getElementById('categorySelect');
const yearSelect = document.getElementById('yearSelect');
const btnResetFilter = document.getElementById('btnResetFilter');
const ordinanceCards = document.querySelectorAll('.ordinance-card, .ordinance-row');

function filterOrdinances() {
  const searchInput = document.getElementById('searchInput');
  const categorySelect = document.getElementById('categorySelect');
  const yearSelect = document.getElementById('yearSelect');
  if (!searchInput || !categorySelect || !yearSelect) return;

  const searchVal = searchInput.value.toLowerCase().trim();
  const categoryVal = categorySelect.value;
  const yearVal = yearSelect.value;

  const items = document.querySelectorAll('.ordinance-card, .ordinance-row');
  items.forEach(card => {
    const cardText = card.innerText.toLowerCase();
    const cardCategory = card.getAttribute('data-category');
    const cardYear = card.getAttribute('data-year');

    const matchesSearch = cardText.includes(searchVal);
    const matchesCategory = !categoryVal || cardCategory === categoryVal;
    const matchesYear = !yearVal || cardYear === yearVal;

    if (matchesSearch && matchesCategory && matchesYear) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}

if (searchInput) searchInput.addEventListener('input', filterOrdinances);
if (categorySelect) categorySelect.addEventListener('change', filterOrdinances);
if (yearSelect) yearSelect.addEventListener('change', filterOrdinances);

if (btnResetFilter) {
  btnResetFilter.addEventListener('click', () => {
    if (searchInput) searchInput.value = '';
    if (categorySelect) categorySelect.value = '';
    if (yearSelect) yearSelect.value = '';
    filterOrdinances();
  });
}

// 5. Details Modal Functions
function openDetailsModal(num, title, date, category, status, desc, filePath) {
  const modalNum = document.getElementById('modalNum');
  const modalTitle = document.getElementById('modalTitle');
  const modalDate = document.getElementById('modalDate');
  const modalCategory = document.getElementById('modalCategory');
  const modalDesc = document.getElementById('modalDesc');
  const detailsModal = document.getElementById('detailsModal');
  const modalPdfBtn = document.getElementById('modalPdfBtn');

  if (modalNum) modalNum.innerText = num;
  if (modalTitle) modalTitle.innerText = title;
  if (modalDate) modalDate.innerText = date;
  if (modalCategory) modalCategory.innerText = category;
  if (modalDesc) modalDesc.innerText = desc;

  if (modalPdfBtn) {
    if (filePath && filePath.trim() !== '') {
      modalPdfBtn.href = '../assets/uploads/policies/' + filePath;
      modalPdfBtn.target = '_blank';
      modalPdfBtn.onclick = null;
    } else {
      modalPdfBtn.href = '#';
      modalPdfBtn.target = '_self';
      modalPdfBtn.onclick = function() { alert('Downloading official ordinance PDF file...'); return false; };
    }
  }

  if (detailsModal) detailsModal.style.display = 'flex';
}

function closeDetailsModal() {
  const detailsModal = document.getElementById('detailsModal');
  if (detailsModal) detailsModal.style.display = 'none';
}

// 6. Contact Form Handler
function handleContactSubmit(e) {
  e.preventDefault();
  alert('Thank you for contacting Manila City Hall Legislative Offices. Your message has been received.');
  const contactForm = document.getElementById('contactForm');
  if (contactForm) contactForm.reset();
}
