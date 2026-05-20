function setProgress(percent, element) {
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
  const progressBars = document.querySelectorAll('.circular-progress');

  progressBars.forEach(bar => {
      const percent = bar.getAttribute('data-percent') || 0;
      
      setProgress(percent, bar);
  });
});

function changeCourse(user_data_id) {
  window.location.href = `${change_course_url}?user_data_id=${user_data_id}`;
}