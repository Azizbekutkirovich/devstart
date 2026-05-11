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

document.addEventListener("DOMContentLoaded", function() {
    // 1. LocalStoragedan ochiq modullar massivini olish
    const openedModules = JSON.parse(localStorage.getItem('opened_modules')) || [];

    // 2. Agar saqlangan modullar bo'lsa, ularni ochish
    if (openedModules.length > 0) {
        // Avval barcha "open" klaslarni olib tashlaymiz (toza boshlash uchun)
        document.querySelectorAll('.module-card').forEach(card => card.classList.remove('open'));

        // Saqlangan har bir ID bo'yicha modulni topib ochamiz
        openedModules.forEach(moduleId => {
            const card = document.getElementById(moduleId);
            if (card) {
                card.classList.add('open');
            }
        });
    }
});

/**
 * Modulni ochish va yopish funksiyasi (Ko'p martalik ochish uchun)
 */
function toggleModule(moduleId) {
    const card = document.getElementById(moduleId);
    card.classList.toggle('open'); // Klasni o'zgartirish (bor bo'lsa oladi, yo'q bo'lsa qo'shadi)

    // LocalStorageni yangilash
    updateStorage();
}

/**
 * Hozirgi ochiq modullarni aniqlab, localStoragega yozish
 */
function updateStorage() {
    const openedModules = [];
    
    // Hozirda "open" klasi bor barcha modullarni yig'ib chiqamiz
    document.querySelectorAll('.module-card.open').forEach(card => {
        openedModules.push(card.id);
    });

    // Massivni matn ko'rinishida saqlaymiz
    localStorage.setItem('opened_modules', JSON.stringify(openedModules));
}