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
//    <script src="app.js"></script>
// ═══════════════════════════════════════════

// ── Qaysi flow ishlatilishini server belgilaydi ──
// Masalan: <meta name="topic-flow" content="quick">
const flowName = document.querySelector('meta[name="topic-flow"]')?.getAttribute('content') || 'default';
FlowManager.init(flowName);

// ── Barcha tugma bosilishlarini ushlash ──────
chat.addEventListener('click', function (e) {
  // Test varianti tanlash
  const option = e.target.closest('.option');
  if (option && chat.contains(option)) {
    selectTestOption(option);
    return;
  }

  const button = e.target.closest('button');
  if (!button) return;

  _handleButtonClick(button.id);
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

// ── Markaziy button router ───────────────────
function _handleButtonClick(btnId) {
  // "Davom etish" — topic ichki tugmasi
  if (btnId === 'continue-topic') {
    handleContinue(event);
    return;
  }

  // Test yakunlash tugmasi — maxsus holat
  if (btnId === 'finishTest') {
    checkingTest();
    return;
  }

  // Javoblarni yuborish tugmasi — maxsus holat
  if (btnId === 'validateTask') {
    checkPracticeTask();
    return;
  }

  // Barcha flow tugmalari: "flow-btn-topic", "flow-btn-quiz" ...
  if (btnId.startsWith('flow-btn-')) {
    FlowManager.handleButton(btnId);
    return;
  }

  console.warn('Noma\'lum tugma:', btnId);
}