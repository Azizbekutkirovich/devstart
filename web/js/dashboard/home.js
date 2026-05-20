function setProgress(percent) {
  const circle = document.querySelector('.cp-fill');
  const text = document.querySelector('.cp-text');
  
  const radius = circle.r.baseVal.value;
  const circumference = 2 * Math.PI * radius;

  circle.style.strokeDasharray = `${circumference} ${circumference}`;

  const offset = circumference - (percent / 100 * circumference);
  circle.style.strokeDashoffset = offset;

  text.innerText = `${percent}%`;
}

setProgress(course_progress_percent);

document.addEventListener("DOMContentLoaded", function() {
    const openedModules = JSON.parse(localStorage.getItem('opened_modules')) || [];

    if (openedModules.length > 0) {
        document.querySelectorAll('.module-card').forEach(card => card.classList.remove('open'));

        openedModules.forEach(moduleId => {
            const card = document.getElementById(moduleId);
            if (card) {
                card.classList.add('open');
            }
        });
    }
});

function toggleModule(moduleId) {
    const card = document.getElementById(moduleId);
    card.classList.toggle('open');

    updateStorage();
}

function updateStorage() {
    const openedModules = [];
    
    document.querySelectorAll('.module-card.open').forEach(card => {
        openedModules.push(card.id);
    });

    localStorage.setItem('opened_modules', JSON.stringify(openedModules));
}