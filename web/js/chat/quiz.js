let quizData = [];

function startTest() {
  const loader    = showLoader(chat);
  const urlParams = new URLSearchParams(window.location.search);
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajax({
    url: 'generate-quiz-test',
    type: 'POST',
    headers: { 'X-CSRF-Token': csrfToken },
    dataType: 'json',
    contentType: 'application/json',
    data: JSON.stringify({
      course_id: urlParams.get('course_id'),
      topic_id: urlParams.get('topic_id'),
      level_id: urlParams.get('level_id'),
      lesson_content: lesson_content,
    }),
    success(response) {
      hideLoader(loader);
      if (!response.success) { addBotMessage('❌ Xatolik: ' + response.message); return; }

      quizData = _parseQuizData(response.data);

      const div = createBotMessageContainer();
      div.classList.add('bot');
      typeText(div,
        "Endi siz uchun 5 ta test topshiriqlari tayyorladim. Tayyor bo'lsangiz boshlaymiz! 🚀",
        () => {
          showQuiz();
          const div2 = createBotMessageContainer();
          div2.classList.add('bot');
          typeText(div2, "Testni yakunlab 👆 keyingi bosqichga o'tishingiz mumkin");
        }
      );
    },
    error() { hideLoader(loader); addBotMessage('❌ Test savollarini yuklashda xatolik.'); },
  });
}

function showQuiz() {
  const quizDiv = document.createElement('div');
  quizDiv.className = 'quiz-container';

  const title = document.createElement('h2');
  title.style.textAlign = 'center';
  title.textContent = 'Test topshiriqlari';
  quizDiv.appendChild(title);

  quizData.forEach((q, i) => {
    const block = document.createElement('div');
    block.className = 'question-block';

    const qDiv = document.createElement('div');
    qDiv.className = 'question';
    qDiv.innerHTML = `<div class="q-text">${i + 1}. ${q.question}</div>`;
    block.appendChild(qDiv);

    q.options.forEach((opt, idx) => {
      const optDiv = document.createElement('div');
      optDiv.className = 'option';
      optDiv.dataset.q   = i;
      optDiv.dataset.idx = idx;
      optDiv.innerHTML = `<div class="opt-text">${opt}</div>`;
      block.appendChild(optDiv);
    });

    quizDiv.appendChild(block);
  });

  const btnDiv = document.createElement('div');
  btnDiv.className = 'center-btn';
  const finishBtn = document.createElement('button');
  finishBtn.id = 'finishTest';
  finishBtn.textContent = 'Testni yakunlash';
  btnDiv.appendChild(finishBtn);
  quizDiv.appendChild(btnDiv);

  chat.appendChild(quizDiv);
}

function selectTestOption(option) {
  const qIndex = option.dataset.q;
  option.closest('.quiz-container')
    .querySelectorAll(`.option[data-q="${qIndex}"]`)
    .forEach((o) => { o.removeAttribute('data-selected'); o.classList.remove('selected'); });
  option.classList.add('selected');
  option.setAttribute('data-selected', 'true');
}

function checkingTest() {
  const quizDiv = document.querySelector('.quiz-container');
  const selectedAnswers = {};

  quizDiv.querySelectorAll('.question-block').forEach((block, i) => {
    const sel = block.querySelector('.option[data-selected="true"]');
    selectedAnswers[i] = sel ? parseInt(sel.dataset.idx) : null;
  });

  const loader    = showLoader(chat);
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const urlParams = new URLSearchParams(window.location.search);

  $.ajax({
    url: 'check-quiz',
    type: 'POST',
    headers: { 'X-CSRF-Token': csrfToken },
    dataType: 'json',
    contentType: 'application/json',
    data: JSON.stringify({
     topic_id: urlParams.get('topic_id'),
     selected: selectedAnswers 
   }),
    success(response) {
      hideLoader(loader);
      if (!response.success) { addBotMessage('❌ Xatolik: ' + response.message); return; }

      const results = response.data.results.map((r) => ({
        ...r, explanation: marked.parse(r.explanation),
      }));

      applyTestResults(results, quizDiv);
      document.getElementById('finishTest').remove();
      quizDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
      FlowManager.stepDone('quiz');
    },
    error() { hideLoader(loader); addBotMessage('❌ Test natijalarini tekshirishda xatolik.'); },
  });
}

function applyTestResults(results, quizDiv) {
  quizDiv.querySelectorAll('.question-block').forEach((block, i) => {
    const res = results[i];
    block.querySelectorAll('.option').forEach((o) => o.classList.remove('correct', 'incorrect'));

    const result = document.createElement('div');
    const expl   = document.createElement('div');

    if (res.status === 'unanswered') {
      result.className  = 'result unanswered-text';
      result.textContent = '❔ Javob berilmadi';
      block.querySelector(`.option[data-idx="${res.correct}"]`).classList.add('correct');
    } else if (res.status === 'correct') {
      result.className  = 'result correct-text';
      result.textContent = "✅ To'g'ri javob";
      block.querySelector(`.option[data-idx="${res.selected}"]`).classList.add('correct');
    } else {
      result.className  = 'result incorrect-text';
      result.textContent = "❌ Noto'g'ri javob";
      block.querySelector(`.option[data-idx="${res.selected}"]`).classList.add('incorrect');
      block.querySelector(`.option[data-idx="${res.correct}"]`).classList.add('correct');
    }

    block.prepend(result);
    expl.innerHTML    = `<i><strong>Izoh: </strong>${res.explanation}</i>`;
    expl.style.marginTop = '8px';
    block.appendChild(expl);
  });
}

function _parseQuizData(data) {
  return data.map((q) => ({
    question: marked.parse(q.question),
    options:  q.options.map((opt) => marked.parse(opt)),
  }));
}
