// Level data
const levelMeta = {
  beginner:  { icon: '🌱', label: 'Boshlang\'ich daraja tanlandi' },
  middle:    { icon: '⚡', label: 'O\'rta daraja tanlandi' },
  advanced:  { icon: '🚀', label: 'Ilg\'or daraja tanlandi' },
};

let selectedLevel = null;

function selectLevel(el, level) {
  document.querySelectorAll('.level-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  selectedLevel = level;

  // Enable start button
  const startBtn = document.getElementById('startBtn');
  startBtn.disabled = false;

  // Update progress bar
  document.getElementById('progressBar').style.width = '100%';

  // Update step indicator
  const dot = document.getElementById('stepDot');
  const label = document.getElementById('stepLabel');
  const hint = document.getElementById('stepHint');
  const checkIcon = document.getElementById('stepCheckIcon');
  dot.textContent = '';
  dot.classList.add('done');
  label.textContent = 'Daraja tanlandi';
  label.classList.add('done');
  checkIcon.style.display = 'block';
}

function startCourse() {
  if (!selectedLevel) return;
  // Navigate to course — replace with actual URL
  const urlParams = new URLSearchParams(window.location.search);
  window.location.href = `add-course?course_id=${urlParams.get('course_id')}&level_id=${selectedLevel}`;
}

// Set initial progress
setTimeout(() => { document.getElementById('progressBar').style.width = '0%'; }, 100);