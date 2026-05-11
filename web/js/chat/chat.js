// ═══════════════════════════════════════════
//  chat.js — Savol berish va navigatsiya
// ═══════════════════════════════════════════
// Bog'liqlik: helpers.js, topic.js (isTyping, typeText)

// ── "Keyingi bosqich" tugmasini boshqarish ───
let _lastRemovedNextBtn = null;

function _getLastNextStepButton() {
  const all = document.querySelectorAll('.center-btn button');
  return all[all.length - 1] || null;
}

// Foydalanuvchi savol berganda yashirilmasligi kerak bo'lgan tugmalar:
// flow tugmalari (flow-btn-*) yashiriladi, forma submit tugmalari saqlanadi.
const PINNED_BUTTONS = ['finishTest', 'validateTask'];

function removeLastNextStepButton() {
  const lastBtn = _getLastNextStepButton();
  const isProtected = !lastBtn || PINNED_BUTTONS.includes(lastBtn.id);

  if (isProtected) return;

  _lastRemovedNextBtn = lastBtn.parentElement.cloneNode(true);
  lastBtn.parentElement.remove();
}

function restoreLastRemovedNextStepButton() {
  if (!_lastRemovedNextBtn) return;
  chat.appendChild(_lastRemovedNextBtn);
  _lastRemovedNextBtn = null;
  scrollToBottom();
}

// ── Savolga javob berish ─────────────────────
async function askQuestionAboutTopic(userQuestion) {
  const messageDiv = createBotMessageContainer();
  const loader = showLoader(chat);

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const urlParams = new URLSearchParams(window.location.search);

    const chatRenderer = makeStreamingRenderer(messageDiv, loader);
    await fetchWithStreaming(
      'ask-question-about-topic',
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
          level: urlParams.get('level'),
          user_question: userQuestion,
        }),
      },
      chatRenderer.onChunk
    );
    chatRenderer.flush(() => {
      const promptDiv = createBotMessageContainer();
      promptDiv.classList.add('bot');
      typeText(
        promptDiv,
        "Agar barchasi tushunarli bo'lgan bo'lsa pastdagi 👇 tugmani bosib keyingi bosqichga o'tishingiz mumkin",
        () => restoreLastRemovedNextStepButton()
      );
    });
  } catch (error) {
    hideLoader(loader);
    addBotMessage('❌ ' + error.message);
  }
}

// ── Foydalanuvchi savol yuborishi ─────────────
function askQuestion() {
  if (isTyping) return;
  const text = userInput.value.trim();
  if (!text) return;

  addUserMessage(text);
  userInput.value = '';

  removeLastNextStepButton();
  askQuestionAboutTopic(text);
}