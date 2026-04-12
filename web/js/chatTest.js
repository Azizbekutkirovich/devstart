const user_btn = document.querySelector(".center-btn");
const chat = document.getElementById('chat');
const chatInput = document.getElementById('chatInput');
const askQuestionBtn = document.getElementById('askQuestionBtn');
const userInput = document.getElementById('userInput');

let lesson_content = "";
let quizData = [];
let practices = "";
let currentStreamController = null; // Streaming jarayonini boshqarish uchun

document.addEventListener("input", function (e) {
  if (e.target.classList.contains("auto-grow")) {
      const el = e.target;

      el.style.height = "auto";
      const newHeight = Math.min(el.scrollHeight, 140);
      el.style.height = newHeight + "px";
  }
});

function showLoader(container) {
  const loaderDiv = document.createElement('div');
  loaderDiv.className = 'loader-wrapper';
  loaderDiv.innerHTML = `
    <div class="robot-loader">🤖</div>
    <div class="loader-text">Bot o'ylayapti, biroz kuting...</div>
  `;
  container.appendChild(loaderDiv);
  return loaderDiv;
}

function hideLoader(loaderDiv) {
  if(loaderDiv) loaderDiv.remove();
}

function scrollToBottom() {
  chat.scrollTo({
    top: chat.scrollHeight,
    behavior: 'smooth'
  });
}

function addUserMessage(text) {
  const div = document.createElement('div');
  div.className = 'message user';
  div.innerText = text;
  chat.appendChild(div);
  scrollToBottom();
}

function addBotMessage(text) {
  const div = document.createElement('div');
  div.className = 'message bot';
  div.innerHTML = text;
  chat.appendChild(div);
  scrollToBottom();
}

// STREAMING UCHUN YANGI FUNKSIYA
function createBotMessageContainer() {
  const div = document.createElement('div');
  div.className = 'message';
  chat.appendChild(div);
  scrollToBottom();
  return div;
}

async function fetchWithStreaming(url, options, onChunk) {
  const response = await fetch(url, options);

  // HTTP xatolarni (500, 404, etc.) ushlash
  if (!response.ok) {
    throw new Error(`Serverda xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!`);
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
      let rawData = match[1].trim();
      lastIndex = regex.lastIndex;

      if (rawData === '[DONE]') {
        return fullContent;
      }

      try {
        const json = JSON.parse(rawData);

        // Serverdan kelgan mantiqiy xato (masalan, validatsiya)
        if (json.error) {
          throw new Error(json.error);
        }

        if (json.content) {
          fullContent += json.content;
          // UI-ga matnni uzatamiz
          onChunk(json.content, fullContent);
        }
      } catch (e) {
        console.warn("JSON parslashda xato:", e);
        // Agar bu haqiqiy Error obyekt bo'lsa (json.error dan kelgan), uni yuqoriga otamiz
        if (e instanceof Error) throw e;
      }
    }
    buffer = buffer.substring(lastIndex);
  }
  return fullContent;
}

function typingEffect(text, callback) {
  isTyping = true;
  askQuestionBtn.disabled = true;
  userInput.disabled = true;
  let i = 0;
  const div = document.createElement('div');
  div.className = 'message bot';
  chat.appendChild(div);
  scrollToBottom();
  const interval = setInterval(() => {
    div.innerHTML = `<span>🤖 ${text.substring(0,i)}</span>`;
    i++;
    scrollToBottom();
    if(i > text.length) {
      clearInterval(interval);
      isTyping = false;
      askQuestionBtn.disabled = false;
      userInput.disabled = false;
      if(callback) callback();
      if(typeof hljs !== 'undefined') {
        hljs.highlightAll();
      }
    }
  }, 2);
}

function createButton(text, id) {
  const btnDiv = document.createElement('div');
  btnDiv.className = 'center-btn';
  const btn = document.createElement('button');
  btn.id = id;
  btn.textContent = text;
  btnDiv.appendChild(btn);
  chat.appendChild(btnDiv);
  scrollToBottom();
}

// STREAMING BILAN MAVZU BOSHLASH

// ============ GLOBAL O'ZGARUVCHILAR ============
let messageQueue = [];        // Tayyor qismlar navbati
let currentBuffer = '';       // Hozirgi yig'ilayotgan qism
let isTyping = false;         // Typing holatda
let isStreaming = false;      // Stream kelayotganmi
let streamFinishedSuccessfully = false;
let messageCount = 0;         // Nechta xabar ko'rsatildi
let firstLoader = null;       // Birinchi loader

// ============ ASOSIY FUNKSIYA ============
async function startTopic() {
  // Reset
  resetState();

  firstLoader = showLoader(chat);
  
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const urlParams = new URLSearchParams(window.location.search);
    
    lesson_content = await fetchWithStreaming('generate-topic', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/event-stream'
      },
      body: JSON.stringify({
        topic_id: urlParams.get('topic_id'),
        level: urlParams.get('level')
      })
    }, handleChunk);
    
    // Stream tugadi
    finishStreaming();
    
  } catch (error) {
    hideLoader(firstLoader);
    addBotMessage("❌ " + error.message);
  }
}

function handleChunk(chunk) {
  isStreaming = true;
  
  // 1. Yangi kelgan chunkni umumiy buferga qo'shamiz
  currentBuffer += chunk;
  
  // 2. Bufer ichida [NEXT] borligini qidiramiz
  while (currentBuffer.includes('[NEXT]')) {
    const parts = currentBuffer.split('[NEXT]');
    
    // [NEXT] dan oldingi tayyor qismni navbatga qo'shamiz
    const completedPart = parts.shift(); // Birinchi elementni oladi
    if (completedPart) {
      addToQueue(completedPart);
    }
    
    // Qolgan qismini buferga qaytaramiz (qolgan [NEXT]lar bo'lishi mumkin)
    currentBuffer = parts.join('[NEXT]');
  }
}

// ============ QUEUE GA QO'SHISH ============
function addToQueue(text) {
  if (!text.trim()) return;
  
  messageQueue.push(text.trim());
  
  // Agar birinchi xabar bo'lsa va typing yo'q bo'lsa - boshlaymiz
  if (messageCount === 0 && !isTyping) {
    showNextMessage();
  }
}

// ============ STREAM TUGADI ============
function finishStreaming() {
  isStreaming = false;
  streamFinishedSuccessfully = true;
  
  // Oxirgi bufferda nimadir qolgan bo'lsa
  if (currentBuffer.trim()) {
    addToQueue(currentBuffer);
    currentBuffer = '';
  }
}

// ============ KEYINGI XABARNI KO'RSATISH ============
function showNextMessage() {
  if (messageQueue.length === 0 || isTyping) return;
  
  isTyping = true;
  const text = messageQueue.shift();
  
  // Birinchi xabar uchun loaderni o'chiramiz
  if (firstLoader) {
    hideLoader(firstLoader);
    firstLoader = null;
  }
  
  // foydalanuvchi xabari sifatida qo'shish
  if (messageCount !== 0) {
    addUserMessage("Davom etish");
  }

  // Yangi container
  const container = createBotMessageContainer();
  container.classList.add('bot');
  
  // Typing effekt
  typeText(container, marked.parse(text), () => {
    messageCount++;
    showButton();
  });
}

// ============ TYPING EFFEKT ============
function typeText(container, html, callback) {
  if (!html) {
    console.error("typeText: 'html' kontenti topilmadi!");
    container.innerHTML = "<span>❌ Xatolik yuz berdi!</span>"
    return;
  }

  let index = 0;
  container.innerHTML = '<span>🤖 </span>';
  
  const interval = setInterval(() => {
    container.innerHTML = `<span>🤖 ${html.substring(0, index)}</span>`;
    index++;
    
    if (index > html.length) {
      clearInterval(interval);
      applyHighlighting(container);
      if (callback) callback();
      chatInput.style.display = "flex";
      isTyping = false;
    }
  }, 2);
}

// ============ TUGMA KO'RSATISH ============
function showButton() {
  // Oxirgi xabar bo'lsa
  const message_before_next_button = createBotMessageContainer();
  message_before_next_button.classList.add('bot');

  if (messageCount >= 5) {
    if (typeof chatInput !== 'undefined') chatInput.style.display = "flex";
    typeText(message_before_next_button, "Pastdagi 👇 tugmani bosib <strong>Mavzuga oid testlar</strong> yechishingiz mumkin", () => {
      createButton("Mavzuga oid testlar", 'startTestBtn');
    });
    return;
  }


  typeText(message_before_next_button, "Pastdagi 👇 tugmani bosib mavzuni davom ettirishingiz mumkin");

  // Davom etish tugmasi
  createButton("Davom etish ➡", "continue-topic");
}

// ============ DAVOM ETISH TUGMASI ============
function handleContinue() {
  event.target.closest('.center-btn').remove();
  
  // Queue'da xabar bor
  if (messageQueue.length > 0) {
    showNextMessage();
    return;
  }
  
  // Queue bo'sh - kutish loaderi
  const loader = createBotMessageContainer();
  loader.classList.add('bot');
  loader.innerHTML = '<span>⏳ Yuklanmoqda...</span>';
  scrollToBottom();
  
  // Har 100ms da tekshiramiz
  const check = setInterval(() => {
    if (messageQueue.length > 0) {
      clearInterval(check);
      loader.remove();
      showNextMessage();
    }
    // Stream tugagan va buffer ham bo'sh
    else if (!isStreaming && messageQueue.length === 0) {
      clearInterval(check);
      if (streamFinishedSuccessfully) {
        loader.remove();
      } else {
        loader.innerHTML = '<span>❌ Xatolik yuz berdi</span>';
      }
    }
  }, 100);
}

// ============ YORDAMCHI FUNKSIYALAR ============
function resetState() {
  messageQueue = [];
  currentBuffer = '';
  isTyping = false;
  isStreaming = false;
  messageCount = 0;
  firstLoader = null;
}

function scrollToBottom() {
  if (typeof chat !== 'undefined') {
    chat.scrollTop = chat.scrollHeight;
  }
}

function applyHighlighting(container) {
  if (typeof hljs !== 'undefined') {
    container.querySelectorAll('pre code').forEach((block) => {
      if (!block.dataset.highlighted) {
        hljs.highlightElement(block);
        block.dataset.highlighted = "true";
      }
    });
  }
}

function startTest() {
  const loader = showLoader(chat);

  const urlParams = new URLSearchParams(window.location.search);
  let topic_id = urlParams.get('topic_id');
  let level = urlParams.get('level');

  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajax({
    url: 'generate-quiz-test',
    type: 'POST',
    headers: {
      'X-CSRF-Token': csrfToken
    },
    dataType: 'json',
    data: JSON.stringify({
      topic_id: topic_id,
      level: level,
      lesson_content: lesson_content
    }),
    contentType: 'application/json',
    success: function(response) {
      hideLoader(loader);
      
      if(response.success) {
        quizData = response.data.map(q => {
          return {
            question: marked.parse(q.question),
            options: q.options.map(opt => marked.parse(opt))
          };
        });
        typingEffect("Endi siz uchun 5 ta test topshiriqlari tayyorladim. Tayyor bo'lsangiz boshlaymiz! 🚀", function() {
          showQuiz();
          typingEffect("Testni yakunlab 👆 keyingi bosqichga o'tishingiz mumkin");
        });
      } else {
        addBotMessage("❌ Xatolik: " + response.message);
      }
    },
    error: function(xhr, status, error) {
      hideLoader(loader);
      addBotMessage("❌ Test savollarini yuklashda xatolik.");
    }
  });
}

// STREAMING BILAN AMALIY TOPSHIRIQLAR
async function startPractice() {
  const messageDiv = createBotMessageContainer();
  const loader = showLoader(chat);
  
  let isFirstChunk = true;

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
          'Accept': 'text/event-stream'
        },
        body: JSON.stringify({
          topic_id: urlParams.get('topic_id'),
          level: urlParams.get('level'),
          lesson_content: lesson_content
        })
      },
      (newChunk, allContent) => {
        // Har bir chunk kelganda bajariladigan UI mantiq
        if (isFirstChunk) {
          hideLoader(loader);
          messageDiv.classList.add('bot');
          isFirstChunk = false;
        }

        // Renderlash
        messageDiv.innerHTML = `<span>🤖 ${marked.parse(allContent)}</span>`;
        
        // Highlighting
        applyHighlighting(messageDiv);
      }
    );

    const practice_inputs = document.createElement('div');
    practice_inputs.id = 'practice-inputs';
    for (let i = 1; i <= 3; i++) {
      const inputDiv = document.createElement('div');
      inputDiv.className = 'practice-input';
      inputDiv.innerHTML = `<textarea placeholder="${i}-topshiriq javobini shu yerga yozing..."></textarea>`;
      practice_inputs.appendChild(inputDiv);
    }
    chat.appendChild(practice_inputs);
    createButton("Topshiriqlarni yuborish", 'validateTask');

  } catch (error) {
    // Barcha turdagi xatolarni markazlashgan holda ushlash
    hideLoader(loader);
    addBotMessage("❌ " + error.message);
  }
}

function showQuiz() {
  const quizDiv = document.createElement('div');
  quizDiv.className = 'quiz-container';

  const title = document.createElement('h2');
  title.style.textAlign = "center";
  title.textContent = "Test topshiriqlari";
  quizDiv.appendChild(title);

  quizData.forEach((q, i) => {
    const block = document.createElement('div');
    block.className = "question-block";

    const question = document.createElement('div');
    question.className = "question";
    question.innerHTML = `<div class="q-text">${i+1}. ${q.question}</div>`;
    block.appendChild(question);

    q.options.forEach((opt, idx) => {
      const optDiv = document.createElement('div');
      optDiv.className = "option";
      optDiv.dataset.q = i;
      optDiv.dataset.idx = idx;
      optDiv.innerHTML = `<div class="opt-text">${opt}</div>`;
      block.appendChild(optDiv);
    });

    quizDiv.appendChild(block);
  });

  const btnDiv = document.createElement('div');
  btnDiv.className = "center-btn";

  const finishBtn = document.createElement('button');
  finishBtn.id = "finishTest";
  finishBtn.textContent = "Testni yakunlash";

  btnDiv.appendChild(finishBtn);
  quizDiv.appendChild(btnDiv);

  chat.appendChild(quizDiv);
}

function selectTestOption(option) {
  const qIndex = option.dataset.q;
  const quizDiv = option.closest(".quiz-container");

  quizDiv.querySelectorAll(`.option[data-q="${qIndex}"]`).forEach(o => {
    o.removeAttribute('data-selected');
    o.classList.remove("selected");
  });

  option.classList.add("selected");
  option.setAttribute('data-selected', 'true');
}

// Test natijalarini tekshirish
function checkingTest() {
  const quizDiv = document.querySelector(".quiz-container");
  const selectedAnswers = {};
  
  quizDiv.querySelectorAll('.question-block').forEach((block, i) => {
    const selected = block.querySelector('.option[data-selected="true"]');
    selectedAnswers[i] = selected ? parseInt(selected.dataset.idx) : null;
  });

  const chatContainer = document.getElementById('chat');
  const loader = showLoader(chatContainer);

  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajax({
    url: 'check-quiz',
    type: 'POST',
    headers: {
      'X-CSRF-Token': csrfToken
    },
    dataType: 'json',
    data: JSON.stringify({
      selected: selectedAnswers
    }),
    contentType: 'application/json',
    success: function(response) {
      hideLoader(loader);
      
      if(response.success) {
        response.data.results = response.data.results.map(r => {
          return {
              ...r,
              explanation: marked.parse(r.explanation)
          };
        });

        applyTestResults(response.data.results, quizDiv, quizData);
        
        document.getElementById('finishTest').remove();
        createButton("Amaliy topshiriq", 'startPracticeBtn');
        
        quizDiv.scrollIntoView({
          behavior: "smooth",
          block: "start"
        });
      } else {
        addBotMessage("❌ Xatolik: " + response.message);
      }
    },
    error: function(xhr, status, error) {
      hideLoader(loader);
      addBotMessage("❌ Test natijalarini tekshirishda xatolik.");
    }
  });
}

function applyTestResults(results, quizDiv, quizData) {
  quizDiv.querySelectorAll('.question-block').forEach((block, i) => {
    const res = results[i];

    const result = document.createElement('div');
    const expl = document.createElement('div');

    block.querySelectorAll('.option').forEach(opt => {
        opt.classList.remove("correct", "incorrect");
    });

    if (res.status === "unanswered") {
        result.className = "result unanswered-text";
        result.textContent = "❔ Javob berilmadi";

        block.querySelector(`.option[data-idx="${res.correct}"]`)
            .classList.add("correct");

    } else if (res.status === "correct") {
        result.className = "result correct-text";
        result.textContent = "✅ To'g'ri javob";

        block.querySelector(`.option[data-idx="${res.selected}"]`)
            .classList.add("correct");

    } else {
        result.className = "result incorrect-text";
        result.textContent = "❌ Noto'g'ri javob";

        block.querySelector(`.option[data-idx="${res.selected}"]`)
            .classList.add("incorrect");

        block.querySelector(`.option[data-idx="${res.correct}"]`)
            .classList.add("correct");
    }

    block.prepend(result);

    expl.innerHTML = `<i><strong>Izoh: </strong>${res.explanation}</i>`;
    expl.style.marginTop = "8px";
    block.appendChild(expl);
  });
}

function getLastNextStepButton() {
  const all = document.querySelectorAll(".center-btn button");
  return all[all.length - 1];
}

let lastRemovedNextStepButton = null;

function removeLastNextStepButton() {
    const lastBtn = getLastNextStepButton();
    if (lastBtn && lastBtn.id !== "finishTest" && lastBtn.id !== "validateTask") {
        lastRemovedNextStepButton = lastBtn.parentElement.cloneNode(true);
        lastBtn.parentElement.remove();
    }
}

function restoreLastRemovedNextStepButton() {
  if (lastRemovedNextStepButton) {
      chat.appendChild(lastRemovedNextStepButton);
      lastRemovedNextStepButton = null;
      scrollToBottom();
  }
}

// STREAMING BILAN TOPSHIRIQLARNI TEKSHIRISH
async function checkPracticeTask() {
  let answers = [];
  let i = 1;
  
  document.querySelectorAll('#practice-inputs .practice-input textarea').forEach((el) => {
    const answer = el.value.trim();
    addUserMessage(`${i}-topshiriq javobi: \n` + answer);
    answers.push({
      task_number: i,
      answer: answer
    });
    i++;
  });

  const chatContainer = document.getElementById('chat');
  const messageDiv = createBotMessageContainer();

  const urlParams = new URLSearchParams(window.location.search);
  let topic_id = urlParams.get('topic_id');
  let level = urlParams.get('level');
  
  isTyping = true;
  askQuestionBtn.disabled = true;
  userInput.disabled = true;

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    const result = await fetchWithStreaming(
      'check-practice',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
          topic_id: topic_id,
          level: level,
          practices: practices,
          answers: answers
        })
      },
      messageDiv
    );
    
    isTyping = false;
    askQuestionBtn.disabled = false;
    userInput.disabled = false;
    
    chatContainer.scrollIntoView({ behavior: "smooth", block: "end" });
    
  } catch (error) {
    console.error('Error:', error);
    messageDiv.innerHTML = `<span style="color: #dc3545;">❌ ${error.message || "Topshiriqlarni yuborishda xatolik."}</span>`;
    isTyping = false;
    askQuestionBtn.disabled = false;
    userInput.disabled = false;
  }
}

async function checkPracticeTask() {
  let answers = [];
  let i = 1;
  
  document.querySelectorAll('#practice-inputs .practice-input textarea').forEach((el) => {
    const answer = el.value.trim();
    addUserMessage(`${i}-topshiriq javobi: \n` + answer);
    answers.push({
      task_number: i,
      answer: answer
    });
    i++;
  });

  const messageDiv = createBotMessageContainer();
  const loader = showLoader(chat);
  
  let isFirstChunk = true;

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
          'Accept': 'text/event-stream'
        },
        body: JSON.stringify({
          topic_id: urlParams.get('topic_id'),
          level: urlParams.get('level'),
          practices: practices,
          answers: answers
        })
      },
      (newChunk, allContent) => {
        // Har bir chunk kelganda bajariladigan UI mantiq
        if (isFirstChunk) {
          hideLoader(loader);
          messageDiv.classList.add('bot');
          isFirstChunk = false;
        }

        // Renderlash
        messageDiv.innerHTML = `<span>🤖 ${marked.parse(allContent)}</span>`;
        
        // Highlighting
        applyHighlighting(messageDiv);
      }
    );

    const message_before_next_button = createBotMessageContainer();
    message_before_next_button.classList.add('bot');
    typeText(message_before_next_button, "Tabriklaymiz 🎉🎉🎉. Siz mavzuni to'liq tugatdingiz. Pastdagi 👇 tugmani bosib keyingi mavzuga o'tishingiz mumkin", () => {
      createButton("Keyingi mavzuga o'tish ➡️", 'nextTopic');
    });

  } catch (error) {
    // Barcha turdagi xatolarni markazlashgan holda ushlash
    hideLoader(loader);
    addBotMessage("❌ " + error.message);
  }
}

// STREAMING BILAN SAVOL BERISH
async function askQuestionAboutTopic(userQuestion) {
  const messageDiv = createBotMessageContainer();
  const loader = showLoader(chat);
  
  let isFirstChunk = true;

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const urlParams = new URLSearchParams(window.location.search);

    await fetchWithStreaming(
      'ask-question-about-topic',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/event-stream'
        },
        body: JSON.stringify({
          topic_id: urlParams.get('topic_id'),
          level: urlParams.get('level'),
          user_question: userQuestion
        })
      },
      (newChunk, allContent) => {
        // Har bir chunk kelganda bajariladigan UI mantiq
        if (isFirstChunk) {
          hideLoader(loader);
          messageDiv.classList.add('bot');
          isFirstChunk = false;
        }

        // Renderlash
        messageDiv.innerHTML = `<span>🤖 ${marked.parse(allContent)}</span>`;
        
        // Highlighting
        applyHighlighting(messageDiv);
      }
  );

  const message_before_next_button = createBotMessageContainer();
  message_before_next_button.classList.add('bot');
  typeText(message_before_next_button, "Agar barchasi tushunarli bo‘lgan bo‘lsa pastdagi 👇 tugmani bosib keyingi bosqichga o'tishingiz mumkin", () => {
    restoreLastRemovedNextStepButton();
  });

  } catch (error) {
    // Barcha turdagi xatolarni markazlashgan holda ushlash
    hideLoader(loader);
    addBotMessage("❌ " + error.message);
  }
}

function askQuestion() {
  if(isTyping) return;
  const text = userInput.value.trim();
  if(!text) return;
  
  addUserMessage(text);
  userInput.value = '';
  
  removeLastNextStepButton();
  askQuestionAboutTopic(text);
}

function app(userMessage, action) {
  isTyping = true;
  const chatContainer = document.getElementById('chat');
  addUserMessage(userMessage);
  action();
  chatContainer.scrollIntoView({ behavior: "smooth", block: "end" });
}

function nextStep(button_id) {
  let button = document.getElementById(button_id);
  if (button_id == 'startTopicBtn') {
    button.remove();
    app("Mavzuni boshlash", startTopic);
  } else if (button_id == 'continue-topic') {
    handleContinue();
  } else if (button_id == 'startTestBtn') {
    button.remove();
    app("Mavzuga oid testlar", startTest);
  } else if (button_id == 'finishTest') {
    checkingTest();
  } else if (button_id == 'startPracticeBtn') {
    button.remove();
    app("Mavzuga oid amaliy topshiriqlar", startPractice);
  } else if (button_id == 'validateTask') {
    checkPracticeTask();
  }
}

chat.addEventListener("click", function(e) {
  const option = e.target.closest(".option");
  if (option && chat.contains(option)) {
    selectTestOption(option);
    return;
  }
  const button = e.target.closest("button");
  if (!button) return;
  nextStep(button.id);
});

chatInput.addEventListener("click", function(e) {
  const button = e.target.closest("button");
  if (!button) return;
  askQuestion();
});

function addCopyButtons(root = document) {
  root.querySelectorAll("pre:not(.copy-added)").forEach((pre) => {
    pre.classList.add("copy-added");

    const button = document.createElement("button");
    button.className = "copy-code-button";
    button.textContent = "Copy";

    pre.style.position = "relative";
    pre.appendChild(button);

    button.addEventListener("click", async () => {
      const code = pre.querySelector("code")?.innerText || "";
      await navigator.clipboard.writeText(code);

      button.textContent = "Copied!";
      button.classList.add("copied");

      setTimeout(() => {
        button.textContent = "Copy";
        button.classList.remove("copied");
      }, 1500);
    });
  });
}

const observer = new MutationObserver((mutations) => {
  for (const mutation of mutations) {
    for (const node of mutation.addedNodes) {
      if (node.nodeType === 1) {
        addCopyButtons(node);
      }
    }
  }
});

observer.observe(document.body, {
  childList: true,
  subtree: true
});

marked.setOptions({
  breaks: true,  // Oddiy \n ni <br> ga aylantiradi
  gfm: true,     // GitHub Flavored Markdown
  headerIds: false,
  mangle: false
});

hljs.highlightAll();
addCopyButtons();