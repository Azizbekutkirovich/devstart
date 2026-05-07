// SET PROGRESS
function setProgress(percent, element) {
  // Faqat berilgan 'element' ichidagi .cp-fill va .cp-text ni qidiradi
  const circle = element.querySelector('.cp-fill');
  const text = element.querySelector('.cp-text');
  
  const radius = circle.r.baseVal.value;
  const circumference = 2 * Math.PI * radius;

  circle.style.strokeDasharray = `${circumference} ${circumference}`;

  const offset = circumference - (percent / 100 * circumference);
  circle.style.strokeDashoffset = offset;

  text.innerText = `${percent}%`;
}

document.addEventListener("DOMContentLoaded", function() {
  // Sahifadagi barcha progress barlarni topamiz
  const progressBars = document.querySelectorAll('.circular-progress');

  progressBars.forEach(bar => {
      // Har bir divdan uning foiz qiymatini olamiz
      // Buning uchun HTMLda data-percent="40" deb yozish qulay
      const percent = bar.getAttribute('data-percent') || 0;
      
      // Funksiyani chaqiramiz
      setProgress(percent, bar);
  });
});