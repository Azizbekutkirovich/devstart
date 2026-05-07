// SET PROGRESS
function setProgress(percent) {
  const circle = document.querySelector('.cp-fill');
  const text = document.querySelector('.cp-text');
  
  // Aylana uzunligini hisoblash (2 * PI * R)
  const radius = circle.r.baseVal.value;
  const circumference = 2 * Math.PI * radius;

  // SVG-ga chiziq uzunligini beramiz
  circle.style.strokeDasharray = `${circumference} ${circumference}`;

  // Progressni hisoblash: (Foizga qarab chiziqni qisqartirish)
  const offset = circumference - (percent / 100 * circumference);
  circle.style.strokeDashoffset = offset;

  // Matnni yangilash
  text.innerText = `${percent}%`;
}

setProgress(course_progress_percent);

// ── MODULE TOGGLE ──
function toggleModule(id) {
  document.getElementById(id).classList.toggle('open');
}