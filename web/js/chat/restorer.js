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

function inferStepFromHistory(history) {
  const FALLBACK = { step: 0, continueTopic: false, practiceInputs: false, isPending: false };
  if (!Array.isArray(history) || history.length === 0) return FALLBACK;

  const types = history.map((h) => h.type);
  const hasTopicParts      = types.includes('bot_topic');
  const hasQuiz            = types.includes('quiz');
  const hasPractice        = types.includes('bot_practice');
  const hasPracticeResult  = types.includes('bot_practice_result');

  if (hasPracticeResult) {
    return { step: 3, continueTopic: false, practiceInputs: false, isPending: false };
  }

  if (hasPractice) {
    return { step: 2, continueTopic: false, practiceInputs: true, isPending: true };
  }

  if (hasQuiz) {
    const quizItem = history.find((h) => h.type === 'quiz');
    const quizDone = quizItem?.data?.results?.length > 0;
    
    if (quizDone) {
        return { step: 2, continueTopic: false, practiceInputs: false, isPending: false };
    }
    return { step: 1, continueTopic: false, practiceInputs: false, isPending: true };
  }

  if (hasTopicParts) {
    const topicCompleted = window.__RESUME__?.topic_completed ?? true;
    if (topicCompleted) {
        return { step: 1, continueTopic: false, practiceInputs: false, isPending: false };
    }
    return { step: 0, continueTopic: true, practiceInputs: false, isPending: true };
  }

  return FALLBACK;
}

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
}

function restorePracticeContent(rawContent) {
  practices = rawContent;

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