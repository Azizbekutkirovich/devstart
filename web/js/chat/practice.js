// ═══════════════════════════════════════════
//  practice.js — Amaliy topshiriqlar
// ═══════════════════════════════════════════
// Bog'liqlik: helpers.js, flowManager.js

let practices = '';

async function startPractice() {
  const messageDiv = createBotMessageContainer();
  const loader     = showLoader(chat);

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const urlParams = new URLSearchParams(window.location.search);

    practices = await fetchWithStreaming(
      'generate-practice',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/event-stream',
        },
        body: JSON.stringify({
          topic_id: urlParams.get('topic_id'),
          level_id: urlParams.get('level_id'),
          lesson_content,
        }),
      },
      makeStreamingRenderer(messageDiv, loader)
    );

    _appendPracticeInputs();
    createButton('Topshiriqlarni yuborish', 'validateTask');
  } catch (error) {
    hideLoader(loader);
    addBotMessage('❌ ' + error.message);
  }
}

async function checkPracticeTask() {
  const answers = [];
  let i = 1;

  document.querySelectorAll('#practice-inputs .practice-input textarea').forEach((el) => {
    const answer = el.value.trim();
    addUserMessage(`${i}-topshiriq javobi:\n${answer}`);
    answers.push({ task_number: i, answer });
    i++;
  });

  const messageDiv = createBotMessageContainer();
  const loader     = showLoader(chat);

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const urlParams = new URLSearchParams(window.location.search);

    await fetchWithStreaming(
      'check-practice',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/event-stream',
        },
        body: JSON.stringify({
          topic_id: urlParams.get('topic_id'),
          level_id: urlParams.get('level_id'),
          practices,
          answers,
        }),
      },
      makeStreamingRenderer(messageDiv, loader)
    );

    // FlowManager "end" stepini ko'rsatadi
    FlowManager.stepDone('practice');
  } catch (error) {
    hideLoader(loader);
    addBotMessage('❌ ' + error.message);
  }
}

function _appendPracticeInputs() {
  document.getElementById('practice-inputs')?.remove();

  const container = document.createElement('div');
  container.id = 'practice-inputs';

  for (let i = 1; i <= 3; i++) {
    const inputDiv = document.createElement('div');
    inputDiv.className = 'practice-input';
    inputDiv.innerHTML = `<textarea placeholder="${i}-topshiriq javobini shu yerga yozing..."></textarea>`;
    container.appendChild(inputDiv);
  }

  chat.appendChild(container);
}
