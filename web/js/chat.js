// CHAT WITH AJAX
const user_btn = document.querySelector(".center-btn");
const chat = document.getElementById('chat');
const chatInput = document.getElementById('chatInput');
const askQuestionBtn = document.getElementById('askQuestionBtn');
const userInput = document.getElementById('userInput');

let lesson_content = "";
let quizData = [];
let practices = "";
let typingInProgress = false;

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
  <div class="loader-wrapper">
    <div class="robot-loader">🤖</div>
    <div class="loader-text">Bot o'ylayapti, biroz kuting...</div>
  </div>
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

function typingEffect(text, callback) {
  typingInProgress = true;
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
    if(i > text.length) {
      clearInterval(interval);
      typingInProgress = false;
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

function startTopic() {
  const chatContainer = document.getElementById('chat');
  const loader = showLoader(chatContainer);
  const urlParams = new URLSearchParams(window.location.search);
  let topic_id = urlParams.get('topic_id');
  let level = urlParams.get('level');
  $.ajax({
    url: 'generate-topic',
    type: 'GET',
    data: {"topic_id": topic_id, 'level': level},
    dataType: 'json',
    success: function(response) {
      hideLoader(loader);

      if(response.success) {
        lesson_content = response.data.content;
        let text = marked.parse(response.data.content);
        typingEffect(text, () => {
          chatInput.style.display = "flex";
          createButton("Mavzuga oid testlar", 'startTestBtn');
        });
      } else {
        addBotMessage("❌ Xatolik: " + response.message);
      }
    },
    error: function(xhr, status, error) {
      hideLoader(loader);
      addBotMessage("❌ Server bilan bog'lanishda xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!");
    }
  });
}

// AJAX: Test savollarini backenddan olish
function startTest() {
  const chatContainer = document.getElementById('chat');
  const loader = showLoader(chatContainer);

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
        typingEffect("Endi siz uchun 5 ta test topshiriqlari mavjud. Tayyor bo'lsangiz boshlaymiz!", function() {
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

// AJAX: Amaliy topshiriqlarni backenddan olish
function startPractice() {
  const chatContainer = document.getElementById('chat');
  const loader = showLoader(chatContainer);

  const urlParams = new URLSearchParams(window.location.search);
  let topic_id = urlParams.get('topic_id');
  let level = urlParams.get('level');

  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajax({
    url: 'generate-practice',
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
        practices = response.data.content;
        let content = marked.parse(response.data.content);
        typingEffect(content, () => {
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
        });
      } else {
        addBotMessage("❌ Xatolik: " + response.message);
      }
    },
    error: function(xhr, status, error) {
      hideLoader(loader);
      addBotMessage("❌ Amaliy topshiriqlarni yuklashda xatolik!");
    }
  });
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
    o.style.borderColor = "#ccc";
    o.removeAttribute('data-selected');
  });

  option.style.borderColor = "#0078ff";
  option.setAttribute('data-selected', 'true');
}

// AJAX: Test natijalarini backendga yuborish va tekshirish
function checkingTest() {
  const quizDiv = document.querySelector(".quiz-container");
  const selectedAnswers = {};
  
  // Foydalanuvchi javoblarini yig'ish
  quizDiv.querySelectorAll('.question-block').forEach((block, i) => {
    const selected = block.querySelector('.option[data-selected="true"]');
    selectedAnswers[i] = selected ? parseInt(selected.dataset.idx) : null;
  });

  const chatContainer = document.getElementById('chat');
  const loader = showLoader(chatContainer);

  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  // AJAX orqali backendga yuborish
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
        // Backend natijalarini UIga qo'llash
        response.data.results = response.data.results.map(r => {
          return {
              ...r,
              explanation: marked.parse(r.explanation)
          };
        });

        applyTestResults(response.data.results, quizDiv, quizData);
        
        // Eski tugmani olib tashlash
        document.getElementById('finishTest').remove();
        
        // Yangi tugma yaratish
        createButton("Amaliy topshiriq", 'startPracticeBtn');
        
        // Scroll tepaga
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

    expl.innerHTML = `<i>${res.explanation}</i>`;
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

// AJAX: Amaliy topshiriq javoblarini backendga yuborish
function checkPracticeTask() {
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
  
  console.log(answers);

  const chatContainer = document.getElementById('chat');
  const loader = showLoader(chatContainer);

  const urlParams = new URLSearchParams(window.location.search);
  let topic_id = urlParams.get('topic_id');
  let level = urlParams.get('level');

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  
  $.ajax({
    url: 'check-practice',
    type: 'POST',
    headers: {
      'X-CSRF-Token': csrfToken
    },
    dataType: 'json',
    data: JSON.stringify({
      topic_id: topic_id,
      level: level,
      practices: practices,
      answers: answers
    }),
    contentType: 'application/json',
    success: function(response) {
      hideLoader(loader);
      
      if(response.success) {
        let content = marked.parse(response.data.content); 
        typingEffect(content);
      } else {
        addBotMessage("❌ Xatolik: " + response.message);
      }
      
      chatContainer.scrollIntoView({ behavior: "smooth", block: "end" });
    },
    error: function(xhr, status, error) {
      hideLoader(loader);
      addBotMessage("❌ Topshiriqlarni yuborishda xatolik.");
    }
  });
}

// AJAX: Foydalanuvchi savolini backendga yuborish
function askQuestionAboutTopic(userQuestion) {
  let lastStepButton = getLastNextStepButton();
  
  const urlParams = new URLSearchParams(window.location.search);
  let topic_id = urlParams.get('topic_id');
  let level = urlParams.get('level');

  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  const chatContainer = document.getElementById('chat');
  const loader = showLoader(chatContainer);
  
  $.ajax({
    url: 'ask-question-about-topic',
    type: 'POST',
    headers: {
      'X-CSRF-Token': csrfToken
    },
    dataType: 'json',
    data: JSON.stringify({
      topic_id: topic_id,
      question: userQuestion,
      level: level
    }),
    contentType: 'application/json',
    success: function(response) {
      hideLoader(loader);
      
      if(response.success) {
        let answer = marked.parse(response.data.answer);
        typingEffect(answer, () => {
          typingEffect("Agar barchasi tushunarli bo‘lgan bo‘lsa pastdagi 👇 tugmani bosib keyingi bosqichga o'tishingiz mumkin", restoreLastRemovedNextStepButton);
        });
      } else {
        addBotMessage("❌ Xatolik: " + response.message);
        typingEffect("Hozircha bu funksiya texnik sabablarga ko'ra ishlamayapti. Agar davom etmoqchi bo'lsangiz pastdagi 👇 tugmani bosib keyingi bosqichga o'tishingiz mumkin", restoreLastRemovedNextStepButton);
      }
      
      chatContainer.scrollIntoView({ behavior: "smooth", block: "end" });
    },
    error: function(xhr, status, error) {
      hideLoader(loader);
      addBotMessage("❌ Savolni yuborishda xatolik.");
      typingEffect("Hozircha bu funksiya texnik sabablarga ko'ra ishlamayapti. Agar davom etmoqchi bo'lsangiz pastdagi 👇 tugmani bosib keyingi bosqichga o'tishingiz mumkin", restoreLastRemovedNextStepButton);
    }
  });
}

function askQuestion() {
  if(typingInProgress) return;
  const text = userInput.value.trim();
  if(!text) return;
  
  addUserMessage(text);
  userInput.value = '';
  
  let lastStepButton = getLastNextStepButton();
  removeLastNextStepButton();
  
  // AJAX orqali savol yuborish
  askQuestionAboutTopic(text);
}

function app(userMessage, action) {
  typingInProgress = true;
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
  } else if (button_id == 'startTestBtn') {
    button.remove();
    app("Mavzuga oid testlar", startTest);
  } else if (button_id == 'finishTest') {
    checkingTest();
  } else if (button_id == 'startPracticeBtn') {
    button.remove();
    app("Mavzuga oid amaliy topshiriqlar", startPractice);
  } else if (button_id == 'validateTask') {
    // button.remove();
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

hljs.highlightAll();
addCopyButtons();