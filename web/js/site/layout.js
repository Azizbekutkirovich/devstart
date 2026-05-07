(function() {
  // Theme
  const html = document.documentElement;
  const saved = localStorage.getItem('devstart-theme');
  if (saved) html.setAttribute('data-theme', saved);
  document.querySelectorAll('.theme-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const isDark = html.getAttribute('data-theme') === 'dark';
      html.setAttribute('data-theme', isDark ? 'light' : 'dark');
      localStorage.setItem('devstart-theme', isDark ? 'light' : 'dark');
    });
  });

  // Burger
  const burger = document.getElementById('navBurger');
  const mobileMenu = document.getElementById('navMobileMenu');
  if (burger && mobileMenu) {
    burger.addEventListener('click', e => {
      e.stopPropagation();
      const open = burger.classList.toggle('open');
      mobileMenu.classList.toggle('open', open);
    });
    document.addEventListener('click', e => {
      if (!mobileMenu.contains(e.target) && !burger.contains(e.target)) {
        burger.classList.remove('open'); mobileMenu.classList.remove('open');
      }
    });
  }

  // Reveal
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
})();

// Blink keyframe
const style = document.createElement('style');
style.textContent = '@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}';
document.head.appendChild(style);