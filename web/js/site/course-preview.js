function toggleModule(header) {
  const item = header.closest('.module-item');
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.module-item.open').forEach(el => el.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}