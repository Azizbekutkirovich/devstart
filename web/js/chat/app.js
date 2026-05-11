// ═══════════════════════════════════════════
//  app.js — Asosiy kirish nuqtasi
//
//  HTML da yuklash tartibi:
//    <script src="helpers.js"></script>
//    <script src="flowManager.js"></script>
//    <script src="topic.js"></script>
//    <script src="quiz.js"></script>
//    <script src="practice.js"></script>
//    <script src="chat.js"></script>
//    <script src="restorer.js"></script>
//    <script src="app.js"></script>
//
//  Backend yuborishi kerak bo'lgan MINIMUM:
//
//  <meta name="topic-flow" content="default">
//
//  <script>
//    window.__USER_ROLE__ = "guest" | "user";
//    window.__RESUME__ = {
//      lesson_content:  "...",
//      practices:       "...",
//      topic_completed: true   ← bitta yangi boolean, shu yetarli
//    };
//    window.__CHAT_HISTORY__ = [...];
//  </script>
// ═══════════════════════════════════════════

// ── 1. Tarixni tiklash ───────────────────────
const _history  = window.__CHAT_HISTORY__;
const _flowName = document.querySelector('meta[name="topic-flow"]')?.getAttribute('content') || 'default';

if (_history?.length) {
  restoreChat(_history);
}

// ── 2. Step ni aniqlash va FlowManager ──────
//    Restore bo'lsa → inferStepFromHistory
//    Yangi chat    → step 0
const _inferred = _history?.length
  ? inferStepFromHistory(_history)
  : { step: 0, continueTopic: false, practiceInputs: false };

if (window.__USER_ROLE__ == 'guest') {
  FlowManager.init(_flowName, 0);
} else {
  FlowManager.init(_flowName, _inferred.step);
}

// ── 3. Qo'shimcha restore holatlari ──────────

// Topic tugamagan: barcha qismlar ko'rsatilgan,
// lekin foydalanuvchi "Davom etish" ni kutmoqda
if (_inferred.continueTopic) {
  createButton('Davom etish ➡', 'continue-topic');
}

// Practice content bor lekin javob yuborilmagan:
// textarea larni va "Yuborish" tugmasini tiklash
if (_inferred.practiceInputs) {
  restorePracticeInputs();
}

// ── 4. Event listeners ───────────────────────
chat.addEventListener('click', function (e) {
  const option = e.target.closest('.option');
  if (option && chat.contains(option)) {
    selectTestOption(option);
    return;
  }

  const button = e.target.closest('button');
  if (button) _handleButtonClick(button.id, e);
});

chatInput.addEventListener('click', function (e) {
  if (e.target.closest('button')) askQuestion();
});

userInput.addEventListener('keydown', function (e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    askQuestion();
  }
});

// ── 5. Button router ─────────────────────────
function _handleButtonClick(btnId, e) {
  if (btnId === 'continue-topic')     { handleContinue(e); return; }
  if (btnId === 'finishTest')         {  checkingTest();    return; }
  if (btnId === 'validateTask')       { _removeBtn(btnId); checkPracticeTask(); return; }
  if (btnId.startsWith('flow-btn-')) { FlowManager.handleButton(btnId); return; }
  console.warn('Noma\'lum tugma:', btnId);
}

function _removeBtn(id) {
  document.getElementById(id)?.closest('.center-btn')?.remove();
}