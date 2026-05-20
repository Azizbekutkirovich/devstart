const chat        = document.getElementById('chat');
const chatInput   = document.getElementById('chatInput');
const userInput   = document.getElementById('userInput');

document.addEventListener('input', function (e) {
  if (!e.target.classList.contains('auto-grow')) return;
  const el = e.target;
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 140) + 'px';
});

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
  scrollToBottom();
}

function createBotMessageContainer() {
  const div = document.createElement('div');
  div.className = 'message';
  chat.appendChild(div);
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

function showLoader(container) {
  const div = document.createElement('div');
  div.className = 'loader-wrapper';
  div.innerHTML = `
    <div class="loader-container">
      <div class="robot-wrapper">
        <img src="${botAvatar}" alt="Robot" class="loader-img">
        <div class="pulse-ring"></div>
      </div>
      <div class="loader-text">AI o'ylayapti<span class="dots">...</span></div>
    </div>
  `;
  container.appendChild(div);
  return div;
}

function hideLoader(loaderDiv) {
  loaderDiv?.remove();
}

function applyHighlighting(container) {
  if (typeof hljs === 'undefined') return;
  container.querySelectorAll('pre code').forEach((block) => {
    if (!block.dataset.highlighted) {
      hljs.highlightElement(block);
      block.dataset.highlighted = 'true';
    }
  });
}

function addCopyButtons(root = document) {
  root.querySelectorAll('pre:not(.copy-added)').forEach((pre) => {
    pre.classList.add('copy-added');
    pre.style.position = 'relative';
    const btn = document.createElement('button');
    btn.className = 'copy-code-button';
    btn.textContent = 'Nusxalash';
    pre.appendChild(btn);
    btn.addEventListener('click', async () => {
      const code = pre.querySelector('code')?.innerText || '';
      await navigator.clipboard.writeText(code);
      btn.textContent = 'Nusxalandi!';
      btn.classList.add('copied');
      setTimeout(() => { btn.textContent = 'Nusxalash'; btn.classList.remove('copied'); }, 1500);
    });
  });
}

const copyObserver = new MutationObserver((mutations) => {
  for (const mutation of mutations)
    for (const node of mutation.addedNodes)
      if (node.nodeType === 1) addCopyButtons(node);
});
copyObserver.observe(document.body, { childList: true, subtree: true });


async function fetchWithStreaming(url, options, onChunk) {
  const response = await fetch(url, options);

  if (!response.ok) {
    throw new Error("Serverda xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!");
  }

  const reader  = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer      = '';
  let fullContent = '';

  while (true) {
    const { done, value } = await reader.read();
    if (done) break;

    buffer += decoder.decode(value, { stream: true });
    const regex = /data: (.*?)\n/g;
    let match, lastIndex = 0;

    while ((match = regex.exec(buffer)) !== null) {
      const rawData = match[1].trim();
      lastIndex = regex.lastIndex;

      if (rawData === '[DONE:more]') return { content: fullContent, hasMore: true  };
      if (rawData === '[DONE:end]' || rawData === '[DONE]') return { content: fullContent, hasMore: false };

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
  return { content: fullContent, hasMore: false };
}

function makeStreamingRenderer(messageDiv, loader) {
  let pendingContent = '';
  let typedIndex     = 0;
  let intervalId     = null;
  let isFirstChunk   = true;
  let onDone         = null;

  function _tick() {
    if (typedIndex >= pendingContent.length) return;

    typedIndex++;
    messageDiv.innerHTML = `<span><img src="${botAvatar}" alt="Robot" style="width: 70px; height: auto; z-index: 2;">
     ${marked.parse(pendingContent.substring(0, typedIndex))}</span>`;

    if (typedIndex >= pendingContent.length && onDone) _finish();
  }

  function _finish() {
    clearInterval(intervalId);
    applyHighlighting(messageDiv);
    onDone?.();
    onDone = null;
  }

  function onChunk(_newChunk, allContent) {
    if (isFirstChunk) {
      hideLoader(loader);
      messageDiv.classList.add('bot');
      intervalId   = setInterval(_tick, 2);
      isFirstChunk = false;
    }
    pendingContent = allContent;
  }

  function flush(callback) {
    onDone = callback || null;
    if (typedIndex >= pendingContent.length) _finish();
  }

  return { onChunk, flush };
}

function nextTopic() {
  const currentUrl = new URL(window.location.href);

  const searchParams = currentUrl.searchParams;
  let topicId = searchParams.get('topic_id');

  if (topicId) {
      let nextTopicId = parseInt(topicId) + 1;

      searchParams.set('topic_id', nextTopicId);

      window.location.href = currentUrl.pathname + '?' + searchParams.toString();
  } else {
      console.error("URL'da topic_id topilmadi.");
  }
}

marked.setOptions({ breaks: true, gfm: true, headerIds: false, mangle: false });
hljs.highlightAll();
addCopyButtons();