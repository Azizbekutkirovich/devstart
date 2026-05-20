const IS_GUEST = window.__USER_ROLE__ === 'guest';

let lesson_content  = window.__RESUME__?.lesson_content || '';

let isTyping  = false;
let firstLoader = null;

let messageQueue   = [];
let currentBuffer  = '';
let isStreaming    = false;
let streamFinishedSuccessfully = false;
let messageCount   = 0;

async function startTopic() {
  _resetTopicState();
  IS_GUEST ? await _startGuest() : await startUser();
}

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

function _showLocalContinueButton() {
  const div = createBotMessageContainer();
  div.classList.add('bot');
  typeText(div, "Pastdagi 👇 tugmani bosib mavzuni davom ettirishingiz mumkin");
  createButton('Davom etish ➡', 'continue-topic');
}

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
  messageQueue   = [];
  currentBuffer  = '';
  isTyping       = false;
  isStreaming    = false;
  streamFinishedSuccessfully = false;
  messageCount   = 0;
  firstLoader    = null;
}