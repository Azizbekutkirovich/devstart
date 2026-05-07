// ═══════════════════════════════════════════
//  topic.js — Mavzu yuklash va ko'rsatish
// ═══════════════════════════════════════════
// Bog'liqlik: helpers.js, flowManager.js

let lesson_content = '';
let messageQueue   = [];
let currentBuffer  = '';
let isTyping       = false;
let isStreaming    = false;
let streamFinishedSuccessfully = false;
let messageCount   = 0;
let firstLoader    = null;

async function startTopic() {
  _resetTopicState();
  firstLoader = showLoader(chat);

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const urlParams = new URLSearchParams(window.location.search);

    lesson_content = await fetchWithStreaming(
      'generate-topic',
      {
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
      },
      _handleTopicChunk
    );

    _finishTopicStream();
  } catch (error) {
    hideLoader(firstLoader);
    addBotMessage('❌ ' + error.message);
  }
}

function _handleTopicChunk(chunk) {
  isStreaming = true;
  currentBuffer += chunk;

  while (currentBuffer.includes('[NEXT]')) {
    const parts     = currentBuffer.split('[NEXT]');
    const completed = parts.shift();
    if (completed) _addToQueue(completed);
    currentBuffer = parts.join('[NEXT]');
  }
}

function _addToQueue(text) {
  if (!text.trim()) return;
  messageQueue.push(text.trim());
  if (messageCount === 0 && !isTyping) showNextMessage();
}

function _finishTopicStream() {
  isStreaming = false;
  streamFinishedSuccessfully = true;
  if (currentBuffer.trim()) {
    _addToQueue(currentBuffer);
    currentBuffer = '';
  }
}

function showNextMessage() {
  if (messageQueue.length === 0 || isTyping) return;

  isTyping = true;
  const text = messageQueue.shift();

  if (firstLoader) {
    hideLoader(firstLoader);
    firstLoader = null;
  }

  if (messageCount !== 0) addUserMessage('Davom etish');

  const container = createBotMessageContainer();
  container.classList.add('bot');

  typeText(container, marked.parse(text), () => {
    messageCount++;
    const isLast = messageQueue.length === 0 && !isStreaming;

    if (isLast) {
      // FlowManager keyingi tugmani o'zi ko'rsatadi
      FlowManager.stepDone('topic');
    } else {
      _showContinueButton();
    }
  });
}

function _showContinueButton() {
  const div = createBotMessageContainer();
  div.classList.add('bot');
  typeText(div, "Pastdagi 👇 tugmani bosib mavzuni davom ettirishingiz mumkin");
  createButton('Davom etish ➡', 'continue-topic');
}

function handleContinue(event) {
  event.target.closest('.center-btn').remove();

  if (messageQueue.length > 0) {
    showNextMessage();
    return;
  }

  const waitDiv = createBotMessageContainer();
  waitDiv.classList.add('bot');
  waitDiv.innerHTML = '<span>⏳ Yuklanmoqda...</span>';
  scrollToBottom();

  const check = setInterval(() => {
    if (messageQueue.length > 0) {
      clearInterval(check);
      waitDiv.remove();
      showNextMessage();
    } else if (!isStreaming) {
      clearInterval(check);
      if (streamFinishedSuccessfully) waitDiv.remove();
      else waitDiv.innerHTML = '<span>❌ Xatolik yuz berdi</span>';
    }
  }, 100);
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

      // SVG kodlarini rasmga aylantirish
      // const svgElements = container.querySelectorAll('pre, code'); // AI ko'pincha kod blokiga o'raydi
      // svgElements.forEach(el => {
      //     const content = el.textContent.trim();
      //     if (content.startsWith('<svg') && content.endsWith('</svg>')) {
      //         const wrapper = document.createElement('div');
      //         wrapper.className = 'svg-diagram-container';
      //         wrapper.style.textAlign = 'center';
      //         wrapper.style.margin = '20px 0';
      //         wrapper.innerHTML = content; // Mana shu joyda matn haqiqiy SVG rasmga aylanadi
      //         el.parentNode.replaceChild(wrapper, el);
      //     }
      // });
      // activateInteractiveSection();
     
      applyHighlighting(container);

      chatInput.style.display = 'flex';
      isTyping = false;
      callback?.();
    }
  }, 2);
}

function _resetTopicState() {
  lesson_content  = '';
  messageQueue    = [];
  currentBuffer   = '';
  isTyping        = false;
  isStreaming     = false;
  streamFinishedSuccessfully = false;
  messageCount    = 0;
  firstLoader     = null;
}


function restoreText(text, message_sender) {
  content = marked.parse(text);
  message_sender(content);
}

// restoreText(`Assalomu alaykum aziz o'quvchi! Bugungi bizning birinchi darsimiz "Kirish: Algoritm tushunchasi"`, addBotMessage);

// flowName = document.querySelector('meta[name="topic-flow"]')?.getAttribute('content') || 'default';
// FlowManager.init(flowName, 1);