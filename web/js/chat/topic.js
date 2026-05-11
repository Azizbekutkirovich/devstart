// ═══════════════════════════════════════════
//  topic.js — Mavzu yuklash (Guest + User rejimi)
// ═══════════════════════════════════════════
// Bog'liqlik: helpers.js, flowManager.js

// ── Rol va resume ma'lumotlari ───────────────
const IS_GUEST = window.__USER_ROLE__ === 'guest';

// lesson_content quiz/practice uchun kerak, resume dan tiklanadi
let lesson_content  = window.__RESUME__?.lesson_content || '';

// ── Shared holat ─────────────────────────────
let isTyping  = false;
let firstLoader = null;

// ── Guest-only holat ─────────────────────────
let messageQueue   = [];
let currentBuffer  = '';
let isStreaming    = false;
let streamFinishedSuccessfully = false;
let messageCount   = 0;

// ── User-only holat ──────────────────────────
// (part_index backendda saqlanadi, frontend bilmaydi)

// ════════════════════════════════════════════
//  PUBLIC: Mavzuni boshlash
// ════════════════════════════════════════════
async function startTopic() {
  _resetTopicState();
  IS_GUEST ? await _startGuest() : await startUser();
}

// ════════════════════════════════════════════
//  GUEST REJIMI
// ════════════════════════════════════════════
async function _startGuest() {
  firstLoader = showLoader(chat);
  try {
    const { content } = await fetchWithStreaming(
      'generate-topic',
      _buildRequest(),
      _onGuestChunk
    );
    lesson_content = content;
    _finishGuestStream();
  } catch (error) {
    hideLoader(firstLoader);
    addBotMessage('❌ ' + error.message);
  }
}

function _onGuestChunk(chunk) {
  isStreaming    = true;
  currentBuffer += chunk;

  while (currentBuffer.includes('[NEXT]')) {
    const parts     = currentBuffer.split('[NEXT]');
    const completed = parts.shift();
    if (completed) _guestEnqueue(completed);
    currentBuffer = parts.join('[NEXT]');
  }
}

function _guestEnqueue(text) {
  if (!text.trim()) return;
  messageQueue.push(text.trim());
  if (messageCount === 0 && !isTyping) _showNextGuestMessage();
}

function _finishGuestStream() {
  isStreaming = false;
  streamFinishedSuccessfully = true;
  if (currentBuffer.trim()) {
    _guestEnqueue(currentBuffer);
    currentBuffer = '';
  }
}

function _showNextGuestMessage() {
  if (messageQueue.length === 0 || isTyping) return;

  isTyping = true;
  const text = messageQueue.shift();

  if (firstLoader) { hideLoader(firstLoader); firstLoader = null; }

  const container = createBotMessageContainer();
  container.classList.add('bot');

  typeText(container, marked.parse(text), () => {
    messageCount++;
    const isLast = messageQueue.length === 0 && !isStreaming;
    isLast ? FlowManager.stepDone('topic') : _showLocalContinueButton();
  });
}

// ── Guest: navbat bo'sh, stream hali kelayapti ─
function _waitForGuestQueue() {
  const waitDiv = createBotMessageContainer();
  waitDiv.classList.add('bot');
  waitDiv.innerHTML = '<span>⏳ Yuklanmoqda...</span>';
  scrollToBottom();

  const check = setInterval(() => {
    if (messageQueue.length > 0) {
      clearInterval(check);
      waitDiv.remove();
      _showNextGuestMessage();
    } else if (!isStreaming) {
      clearInterval(check);
      if (streamFinishedSuccessfully) waitDiv.remove();
      else waitDiv.innerHTML = '<span>❌ Xatolik yuz berdi</span>';
    }
  }, 100);
}

// ════════════════════════════════════════════
//  USER REJIMI
// ════════════════════════════════════════════
async function startUser() {
  const messageDiv = createBotMessageContainer();
  const loader = showLoader(chat);

  try {
    const topicRenderer = makeStreamingRenderer(messageDiv, loader);
    const { content, hasMore } = await fetchWithStreaming(
      'generate-topic',
      _buildRequest(),
      topicRenderer.onChunk
    );
    lesson_content += content;
    topicRenderer.flush(() => {
      hasMore ? _showLocalContinueButton() : FlowManager.stepDone('topic');
    });
  } catch (error) {
    hideLoader(loader);
    addBotMessage('❌ ' + error.message);
  }
}

async function _fetchUserPart() {
  const messageDiv = createBotMessageContainer();
  const loader = showLoader(chat);

  try {
    const topicRenderer = makeStreamingRenderer(messageDiv, loader);
    const { content, hasMore } = await fetchWithStreaming(
      'continue-topic',
      _buildRequest(),
      topicRenderer.onChunk
    );
    lesson_content += content;
    topicRenderer.flush(() => {
      hasMore ? _showLocalContinueButton() : FlowManager.stepDone('topic');
    });
  } catch (error) {
    hideLoader(loader);
    addBotMessage('❌ ' + error.message);
  }
}

// ════════════════════════════════════════════
//  SHARED: "Davom etish" tugmasi va bosilishi
// ════════════════════════════════════════════

// Qismlar orasidagi mahalliy "Davom etish" (FlowManager tugmasi emas)
function _showLocalContinueButton() {
  const div = createBotMessageContainer();
  div.classList.add('bot');
  typeText(div, "Pastdagi 👇 tugmani bosib mavzuni davom ettirishingiz mumkin");
  createButton('Davom etish ➡', 'continue-topic');
}

// app.js tomonidan chaqiriladi
function handleContinue(event) {
  event.target.closest('.center-btn').remove();
  addUserMessage('Davom etish');

  if (IS_GUEST) {
    if (messageQueue.length > 0) _showNextGuestMessage();
    else _waitForGuestQueue();
  } else {
    _fetchUserPart();
  }
}

// ════════════════════════════════════════════
//  Typing effekt (topic + quiz uchun umumiy)
// ════════════════════════════════════════════
function typeText(container, html, callback) {
  if (!html) {
    container.innerHTML = '<span>❌ Xatolik yuz berdi!</span>';
    return;
  }

  let index = 0;
  container.innerHTML = `<span><img src="${botAvatar}" alt="Robot" style="width: 70px; height: auto; z-index: 2;">`;

  const interval = setInterval(() => {
    container.innerHTML = `<span><img src="${botAvatar}" alt="Robot" style="width: 70px; height: auto; z-index: 2;"> ${html.substring(0, index)}</span>`;
    index++;

    if (index > html.length) {
      clearInterval(interval);
      applyHighlighting(container);
      chatInput.style.display = 'flex';
      isTyping = false;
      callback?.();
    }
  }, 2);
}

// ════════════════════════════════════════════
//  Yordamchilar
// ════════════════════════════════════════════
function _buildRequest(extra = {}) {
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
      level_id: urlParams.get('level_id')
    }),
  };
}

function _resetTopicState() {
  // lesson_content saqlanadi — resume uchun kerak
  messageQueue   = [];
  currentBuffer  = '';
  isTyping       = false;
  isStreaming    = false;
  streamFinishedSuccessfully = false;
  messageCount   = 0;
  firstLoader    = null;
  // currentPartIndex saqlanadi — user rejimida resume uchun
}