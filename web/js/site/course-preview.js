// Module accordion
function toggleModule(header) {
  const item = header.closest('.module-item');
  const isOpen = item.classList.contains('open');
  // Close all
  document.querySelectorAll('.module-item.open').forEach(el => el.classList.remove('open'));
  // Toggle clicked
  if (!isOpen) item.classList.add('open');
}