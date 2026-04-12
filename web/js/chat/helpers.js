// ═══════════════════════════════════════════
//  helpers.js — Umumiy yordamchi funksiyalar
// ═══════════════════════════════════════════

const chat = document.getElementById('chat');
const chatInput = document.getElementById('chatInput');
const askQuestionBtn = document.getElementById('askQuestionBtn');
const userInput = document.getElementById('userInput');

// ── Auto-grow textarea ──────────────────────
document.addEventListener('input', function (e) {
  if (!e.target.classList.contains('auto-grow')) return;
  const el = e.target;
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 140) + 'px';
});

// ── Chat DOM yordamchilari ──────────────────
function scrollToBottom() {
  chat.scrollTop = chat.scrollHeight;
}

function addUserMessage(text) {
  const div = document.createElement('div');
  div.className = 'message user';
  div.innerText = text;
  chat.appendChild(div);
  scrollToBottom();
}

function addBotMessage(html) {
  const div = document.createElement('div');
  div.className = 'message bot';
  div.innerHTML = html;
  chat.appendChild(div);
  // scrollToBottom();
}

function createBotMessageContainer() {
  const div = document.createElement('div');
  div.className = 'message';
  chat.appendChild(div);
  // scrollToBottom();
  return div;
}

function createButton(text, id) {
  const btnDiv = document.createElement('div');
  btnDiv.className = 'center-btn';
  const btn = document.createElement('button');
  btn.id = id;
  btn.textContent = text;
  btnDiv.appendChild(btn);
  chat.appendChild(btnDiv);
}

// ── Loader ──────────────────────────────────
function showLoader(container) {
  const div = document.createElement('div');
  div.className = 'loader-wrapper';
  div.innerHTML = `
    <div class="robot-loader">🤖</div>
    <div class="loader-text">Bot o'ylayapti, biroz kuting...</div>
  `;
  container.appendChild(div);
  return div;
}

function hideLoader(loaderDiv) {
  loaderDiv?.remove();
}

// ── Kod highlight ───────────────────────────
function applyHighlighting(container) {
  if (typeof hljs === 'undefined') return;
  container.querySelectorAll('pre code').forEach((block) => {
    if (!block.dataset.highlighted) {
      hljs.highlightElement(block);
      block.dataset.highlighted = 'true';
    }
  });
}

// ── Copy tugmalari ──────────────────────────
function addCopyButtons(root = document) {
  root.querySelectorAll('pre:not(.copy-added)').forEach((pre) => {
    pre.classList.add('copy-added');
    pre.style.position = 'relative';

    const btn = document.createElement('button');
    btn.className = 'copy-code-button';
    btn.textContent = 'Copy';
    pre.appendChild(btn);

    btn.addEventListener('click', async () => {
      const code = pre.querySelector('code')?.innerText || '';
      await navigator.clipboard.writeText(code);
      btn.textContent = 'Copied!';
      btn.classList.add('copied');
      setTimeout(() => {
        btn.textContent = 'Copy';
        btn.classList.remove('copied');
      }, 1500);
    });
  });
}

// Yangi qo'shilgan elementlarga avtomatik copy tugma
const copyObserver = new MutationObserver((mutations) => {
  for (const mutation of mutations) {
    for (const node of mutation.addedNodes) {
      if (node.nodeType === 1) addCopyButtons(node);
    }
  }
});
copyObserver.observe(document.body, { childList: true, subtree: true });

// ── SSE Streaming ───────────────────────────
/**
 * @param {string} url
 * @param {RequestInit} options
 * @param {(chunk: string, full: string) => void} onChunk
 * @returns {Promise<string>} to'liq kontent
 */
async function fetchWithStreaming(url, options, onChunk) {
  const response = await fetch(url, options);

  if (!response.ok) {
    throw new Error("Serverda xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!");
  }

  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer = '';
  let fullContent = '';

  while (true) {
    const { done, value } = await reader.read();
    if (done) break;

    buffer += decoder.decode(value, { stream: true });
    const regex = /data: (.*?)\n/g;
    let match;
    let lastIndex = 0;

    while ((match = regex.exec(buffer)) !== null) {
      const rawData = match[1].trim();
      lastIndex = regex.lastIndex;

      if (rawData === '[DONE]') return fullContent;

      try {
        const json = JSON.parse(rawData);
        if (json.error) throw new Error(json.error);
        if (json.content) {
          fullContent += json.content;
          onChunk(json.content, fullContent);
        }
      } catch (e) {
        if (e instanceof Error && e.message !== 'Unexpected end of JSON input') throw e;
      }
    }
    buffer = buffer.substring(lastIndex);
  }
  return fullContent;
}

// ── Streaming rendering yordamchisi ─────────
/**
 * fetchWithStreaming uchun standart onChunk handler.
 * Loader yashiradi va messageDiv ni render qiladi.
 */
function makeStreamingRenderer(messageDiv, loader) {
  let isFirstChunk = true;
  return function onChunk(_newChunk, allContent) {
    if (isFirstChunk) {
      hideLoader(loader);
      messageDiv.classList.add('bot');
      isFirstChunk = false;
    }
    messageDiv.innerHTML = `<span>🤖 ${marked.parse(allContent)}</span>`;
    applyHighlighting(messageDiv);
  };
}

// ── Marked konfiguratsiyasi ──────────────────
marked.setOptions({ breaks: true, gfm: true, headerIds: false, mangle: false });
hljs.highlightAll();
addCopyButtons();
