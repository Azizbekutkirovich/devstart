let selectedLevelId = null;
function selectLevel(el, level_id) {
  document.querySelectorAll('.level-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  selectedLevelId = level_id;
  document.getElementById('nextBtn').disabled = false;
}

function goStep2() {
  if (!selectedLevelId) return;
  document.getElementById('step1').classList.remove('active');
  document.getElementById('step2').classList.add('active');
  document.getElementById('dot-1').classList.remove('active');
  document.getElementById('dot-1').classList.add('done');
  document.getElementById('conn-1').classList.add('done');
  document.getElementById('dot-2').classList.add('active');
  document.getElementById('lbl-1').classList.remove('active');
  document.getElementById('lbl-1').classList.add('done');
  document.getElementById('lbl-2').classList.add('active');
  document.getElementById('progressBar').style.width = '100%';
}

function goStep1() {
  document.getElementById('step2').classList.remove('active');
  document.getElementById('step1').classList.add('active');
  document.getElementById('dot-1').classList.add('active');
  document.getElementById('dot-1').classList.remove('done');
  document.getElementById('conn-1').classList.remove('done');
  document.getElementById('dot-2').classList.remove('active');
  document.getElementById('lbl-1').classList.add('active');
  document.getElementById('lbl-1').classList.remove('done');
  document.getElementById('lbl-2').classList.remove('active');
  document.getElementById('progressBar').style.width = '0%';
}

setTimeout(() => { document.getElementById('progressBar').style.width = '0%'; }, 100);

function openModal(e) { e.preventDefault(); document.getElementById('modalOverlay').classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); document.body.style.overflow = ''; }
function closeModalOutside(e) { if (e.target === document.getElementById('modalOverlay')) closeModal(); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

$('#try-btn').on('click', function () {
    let redirectUrl = 'http://localhost/devstart/dashboard/home-preview?course_id=' + encodeURIComponent(course_id) + "&level_id="  + encodeURIComponent(selectedLevelId);
    window.location.href = redirectUrl;
});