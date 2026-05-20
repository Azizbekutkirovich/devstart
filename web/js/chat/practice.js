let practices = window.__RESUME__?.practices || '';

async function startPractice() {
  const messageDiv = createBotMessageContainer();
  const loader     = showLoader(chat);

  try {
    const practiceRenderer = makeStreamingRenderer(messageDiv, loader);
    const { content } = await fetchWithStreaming(
      'generate-practice',
      _buildPracticeRequest(),
      practiceRenderer.onChunk
    );
    practices = content;
    practiceRenderer.flush(() => {
      appendPracticeInputs();
      createButton('Topshiriqlarni yuborish', 'validateTask');
    });
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
    const checkRenderer = makeStreamingRenderer(messageDiv, loader);
    await fetchWithStreaming(
      'check-practice',
      _buildPracticeRequest({ answers }),
      checkRenderer.onChunk
    );
    checkRenderer.flush(() => FlowManager.stepDone('practice'));
  } catch (error) {
    hideLoader(loader);
    addBotMessage('❌ ' + error.message);
  }
}

function appendPracticeInputs() {
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

function _buildPracticeRequest(extra = {}) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const urlParams = new URLSearchParams(window.location.search);
  return {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'text/event-stream',
    },
    body: JSON.stringify({
      course_id: urlParams.get('course_id'),
      topic_id: urlParams.get('topic_id'),
      level_id: urlParams.get('level_id'),
      lesson_content,
      practices,
      ...extra,
    }),
  };
}

function restorePracticeInputs() {
  appendPracticeInputs();
  createButton('Topshiriqlarni yuborish', 'validateTask');
}