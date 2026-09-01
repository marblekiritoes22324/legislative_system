document.addEventListener("DOMContentLoaded", function () {
  // ── Collapsible Sidebar Toggle & localStorage Persistence ──
  const toggleBtns = document.querySelectorAll('.sidebar-toggle-btn, #sidebarToggleBtn');
  const isCollapsed = localStorage.getItem('admin_sidebar_collapsed') === 'true';
  if (isCollapsed) {
    document.body.classList.add('sidebar-collapsed');
    document.documentElement.classList.add('sidebar-collapsed');
  }

  toggleBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const isCurrentlyCollapsed = document.body.classList.contains('sidebar-collapsed');
      const newState = !isCurrentlyCollapsed;
      document.body.classList.toggle('sidebar-collapsed', newState);
      document.documentElement.classList.toggle('sidebar-collapsed', newState);
      localStorage.setItem('admin_sidebar_collapsed', newState ? 'true' : 'false');
    });
  });

  const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
  const navSubLinks = document.querySelectorAll('.sidebar-nav .nav-sub-link');
  const sections = document.querySelectorAll('.content-section');
  const darkModeToggle = document.getElementById('darkModeToggle');
  const profileMenuItems = document.querySelectorAll('#profileDropdown + .dropdown-menu [data-target]');
  const notificationBtn = document.getElementById('notificationButton');

  const showSection = (sectionId) => {
    sections.forEach((section) => {
      section.classList.toggle('d-none', section.id !== sectionId);
    });
    navLinks.forEach((link) => {
      const target = link.dataset.target;
      link.classList.toggle('active', target === sectionId);
    });
  };

  navLinks.forEach((link) => {
    link.addEventListener('click', (event) => {
      const targetId = event.currentTarget.dataset.target;
      if (!targetId) return;
      
      event.preventDefault();
      showSection(targetId);
      navSubLinks.forEach(sub => sub.classList.remove('active'));
    });
  });

  navSubLinks.forEach((subLink) => {
    subLink.addEventListener('click', (event) => {
      const targetId = event.currentTarget.dataset.target;
      if (!targetId) return;
      
      event.preventDefault();
      showSection(targetId);
      navSubLinks.forEach(sub => sub.classList.remove('active'));
      event.currentTarget.classList.add('active');
    });
  });

  if (notificationBtn) {
    notificationBtn.addEventListener('click', () => {
      showSection('notificationsSection');
    });
  }

  profileMenuItems.forEach((item) => {
    item.addEventListener('click', (event) => {
      const targetId = event.currentTarget.dataset.target;
      if (!targetId) return;
      event.preventDefault();
      showSection(targetId);
    });
  });

  if (darkModeToggle) {
    darkModeToggle.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      const icon = darkModeToggle.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-moon-fill');
        icon.classList.toggle('bi-sun-fill');
      }
    });
  }

  const policyTrendsEl = document.getElementById('policyTrendsChart');
  if (policyTrendsEl && typeof Chart !== 'undefined') {
    new Chart(policyTrendsEl.getContext('2d'), {
      type: 'line',
      data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8', 'Week 9', 'Week 10', 'Week 11', 'Week 12'],
        datasets: [{
          label: 'Policy research submissions',
          data: [18, 22, 24, 28, 27, 33, 35, 38, 36, 40, 44, 48],
          borderColor: '#0B2E59',
          backgroundColor: 'rgba(11, 46, 89, 0.12)',
          tension: 0.3,
          fill: true,
          pointRadius: 3,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, grid: { color: 'rgba(15,23,42,0.08)' } }
        }
      }
    });
  }

  const performanceEl = document.getElementById('performanceChart');
  if (performanceEl && typeof Chart !== 'undefined') {
    new Chart(performanceEl.getContext('2d'), {
      type: 'bar',
      data: {
        labels: ['Health', 'Transport', 'Environment', 'Education', 'Social Services'],
        datasets: [{
          label: 'Converted policies',
          data: [12, 18, 9, 10, 14],
          backgroundColor: ['#0B2E59', '#1c4a80', '#3b6c9b', '#D4AF37', '#e3ca67'],
          borderRadius: 12,
          barThickness: 22
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, grid: { color: 'rgba(15,23,42,0.08)' } }
        }
      }
    });
  }

  const categoryDistributionEl = document.getElementById('categoryDistributionChart');
  if (categoryDistributionEl && typeof Chart !== 'undefined') {
    new Chart(categoryDistributionEl.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Transportation', 'Environment', 'Health', 'Social', 'Education'],
        datasets: [{
          data: [28, 22, 18, 16, 16],
          backgroundColor: ['#0B2E59', '#245a9e', '#D4AF37', '#C62828', '#8c1f1f'],
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } }
      }
    });
  }

  const initiativePieEl = document.getElementById('initiativePieChart');
  if (initiativePieEl && typeof Chart !== 'undefined') {
    new Chart(initiativePieEl.getContext('2d'), {
      type: 'pie',
      data: {
        labels: ['Actual', 'Proposed'],
        datasets: [{
          data: [64, 36],
          backgroundColor: ['#0B2E59', '#D4AF37'],
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } } }
      }
    });
  }

  const defaultDashboard = document.getElementById('dashboardSection');
  if (defaultDashboard) {
    showSection('dashboardSection');
  }
});
