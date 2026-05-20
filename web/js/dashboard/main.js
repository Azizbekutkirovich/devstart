document.addEventListener("DOMContentLoaded", function() {
    const currentUrl = window.location.pathname + window.location.search;

    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');

        if (currentUrl === linkHref) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});

const burger = document.getElementById('burger');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

burger.addEventListener('click', () => {
  sidebar.classList.toggle('open');
  overlay.classList.toggle('show');
});
overlay.addEventListener('click', () => {
  sidebar.classList.remove('open');
  overlay.classList.remove('show');
});

const THEME_KEY = 'dashboard_theme';

function applyTheme(theme) {
  document.body.classList.toggle('light-mode', theme === 'light');
  const icon = document.getElementById('themeIcon');
  const label = document.getElementById('themeLabel');
  if (icon)  icon.textContent  = theme === 'light' ? '🌙' : '☀️';
  if (label) label.textContent = theme === 'light' ? "Qorong'i fon" : "Yorug' fon";
}

function toggleTheme() {
  const current = localStorage.getItem(THEME_KEY) || 'dark';
  const next = current === 'dark' ? 'light' : 'dark';
  localStorage.setItem(THEME_KEY, next);
  applyTheme(next);
}

applyTheme(localStorage.getItem(THEME_KEY) || 'light');