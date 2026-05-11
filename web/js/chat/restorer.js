// ═══════════════════════════════════════════
//  restorer.js — Chat tarixini tiklash
//
//  History item turlari (backend yuboradi):
//    bot_topic           — mavzu qismi (markdown)
//    user_message        — foydalanuvchi xabari
//    quiz                — savollar + natijalar
//    bot_practice        — amaliy topshiriq matni
//    bot_practice_result — tekshiruv natijasi
//    bot_message         — boshqa mentor xabarlari
//
//  Backend __RESUME__ da faqat bitta extra field yuboradi:
//    topic_completed: true | false
// ═══════════════════════════════════════════

// ── Bosh tiklovchi ───────────────────────────
function restoreChat(history) {
  if (!Array.isArray(history) || history.length === 0) return;

  history.forEach((item) => {
    switch (item.type) {
      case 'bot_topic':         restoreBotMessage(item.content);    break;
      case 'user_message':              restoreUserMessage(item.content);     break;
      case 'bot_message':       restoreBotMessage(item.content);      break;
      case 'quiz':              restoreQuiz(item.data);               break;
      case 'bot_practice':      restorePracticeContent(item.content); break;
      case 'bot_practice_result': restorePracticeResult(item.content); break;
      default: console.warn('restoreChat: noma\'lum tip:', item.type);
    }
  });
}

// ════════════════════════════════════════════
//  inferStepFromHistory
//
//  History ga qarab FlowManager uchun keyingi
//  step ni va qo'shimcha amallarni aniqlaydi.
//
//  Qaytaradi:
//  {
//    step: number,
//      — FlowManager.init ga beriladigan boshlang'ich step
//      — 99 = FlowManager hech narsa ko'rsatmaydi
//          (tugma allaqachon DOM da yoki boshqa holatda)
//
//    continueTopic: boolean,
//      — true bo'lsa topic hali tugamagan:
//        "Davom etish" tugmasini ko'rsatish kerak
//
//    practiceInputs: boolean
//      — true bo'lsa practice content bor lekin javob yo'q:
//        textarea va "Yuborish" tugmasini tiklash kerak
//  }
// ════════════════════════════════════════════
function inferStepFromHistory(history) {
  const FALLBACK = { step: 0, continueTopic: false, practiceInputs: false };

  if (!Array.isArray(history) || history.length === 0) return FALLBACK;

  const types = history.map((h) => h.type);

  const hasTopicParts      = types.includes('bot_topic');
  const hasQuiz            = types.includes('quiz');
  const hasPractice        = types.includes('bot_practice');
  const hasPracticeResult  = types.includes('bot_practice_result');

  // Practice tekshiruvi tugagan → "Keyingi mavzu" tugmasi
  if (hasPracticeResult) {
    return { step: 3, continueTopic: false, practiceInputs: false };
  }

  // Practice content bor, lekin natija yo'q → inputs va "Yuborish" tugmasini tiklash
  if (hasPractice) {
    return { step: 99, continueTopic: false, practiceInputs: true };
  }

  // Quiz bor
  if (hasQuiz) {
    const quizItem = history.find((h) => h.type === 'quiz');
    const quizDone = quizItem?.data?.results?.length > 0;

    // Quiz natijasi bor → "Amaliy topshiriq" tugmasi
    if (quizDone) return { step: 2, continueTopic: false, practiceInputs: false };

    // Quiz natijasi yo'q → quiz UI da "Testni yakunlash" ko'rinib turibdi
    return { step: 99, continueTopic: false, practiceInputs: false };
  }

  // Faqat topic qismlari bor
  if (hasTopicParts) {
    const topicCompleted = window.__RESUME__?.topic_completed ?? true;

    // Topic tugagan → "Testlarni boshlash" tugmasi
    if (topicCompleted) return { step: 1, continueTopic: false, practiceInputs: false };

    // Topic hali davom etmoqda → "Davom etish" tugmasi
    return { step: 99, continueTopic: true, practiceInputs: false };
  }

  // Hech narsa yo'q → yangi chat
  return FALLBACK;
}

// ── Alohida tiklovchilar ─────────────────────

function restoreUserMessage(text) {
  const div = document.createElement('div');
  div.className = 'message user';
  div.innerText = text;
  chat.appendChild(div);
}

function restoreBotMessage(rawContent) {
  const div = createBotMessageContainer();
  div.classList.add('bot');
  div.innerHTML = `<span><img src="${botAvatar}" alt="Robot" style="width: 70px; height: auto; z-index: 2;"> ${marked.parse(rawContent)}</span>`;
  applyHighlighting(div);
}

function restoreQuiz(data) {
  quizData = data.questions.map((q) => ({
    question: marked.parse(q.question),
    options:  q.options.map((opt) => marked.parse(opt)),
  }));

  showQuiz();

  if (data.results?.length > 0) {
    const parsed = data.results.map((r) => ({
      ...r,
      explanation: marked.parse(r.explanation),
    }));
    document.getElementById('finishTest')?.remove();
    applyTestResults(parsed, document.querySelector('.quiz-container'));
  }
  // results yo'q → "Testni yakunlash" tugmasi DOM da qoladi
}

function restorePracticeContent(rawContent) {
  practices = rawContent; // practice.js global — checkPracticeTask() uchun kerak

  const div = createBotMessageContainer();
  div.classList.add('bot');
  div.innerHTML = `<span><img src="${botAvatar}" alt="Robot" style="width: 70px; height: auto; z-index: 2;"> ${marked.parse(rawContent)}</span>`;
  applyHighlighting(div);
}

function restorePracticeResult(rawContent) {
  const div = createBotMessageContainer();
  div.classList.add('bot');
  div.innerHTML = `<span><img src="${botAvatar}" alt="Robot" style="width: 70px; height: auto; z-index: 2;"> ${marked.parse(rawContent)}</span>`;
  applyHighlighting(div);
}