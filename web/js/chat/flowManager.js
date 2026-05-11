// ═══════════════════════════════════════════
//  flowManager.js — Qadamlar oqimini boshqarish
//
//  Qoidalar:
//  • Har bir step faqat stepDone(stepId) chaqiradi
//  • Keyingi nima bo'lishini faqat shu fayl biladi
//  • Yangi flow qo'shish = flows obektiga 1 qator
// ═══════════════════════════════════════════

// ── Flow konfiguratsiyalari ──────────────────
//
//  Har bir step:
//    id        — stepDone() ga beriladigan nom
//    label     — tugma matni
//    action    — bosilganda chaqiriladigan funksiya nomi (string)
//    removeBtn — bosilgandan oldin button o'chirilsinmi?
//
const FLOWS = {
  // lesson
  lesson: [
    { id: 'topic',    label: 'Darsni boshlash',              action: 'startTopic',    removeBtn: true  },
    { id: 'quiz',     label: 'Quiz testlar',           action: 'startTest',     removeBtn: true  },
    { id: 'practice', label: 'Amaliy topshiriqlar', action: 'startPractice', removeBtn: true  },
    { id: 'end',      label: "Keyingi darsga o'tish ➡️",    action: 'nextTopic',     removeBtn: false },
  ],

  theory: [
    { id: 'topic', label: 'Darsni boshlash',           action: 'startTopic', removeBtn: true },
    { id: 'end',   label: "Keyingi darsga o'tish ➡️", action: 'nextTopic',  removeBtn: false },
  ],

  setup: [
    { id: 'topic', label: 'Darsni boshlash',           action: 'startTopic', removeBtn: true },
    { id: 'end',   label: "Keyingi darsga o'tish ➡️", action: 'nextTopic',  removeBtn: false },
  ],
};

// ── FlowManager ──────────────────────────────
const FlowManager = (() => {
  let _steps    = [];    // Joriy oqim qadamlari
  let _index    = -1;    // Hozirgi qadam indeksi
  let _flowName = '';

  /**
   * Oqimni boshlash.
   * @param {keyof FLOWS} flowName  — qaysi oqim ishlatilsin
   * @param {number} [startFrom=0] — qaysi qadamdan boshlansin (resume uchun)
   */
  function init(flowName = 'default', startFrom = 0) {
    _flowName = flowName;
    _steps    = FLOWS[flowName] ?? FLOWS.default;
    _index    = startFrom - 1; // stepDone() birinchi marta +1 qiladi
    _showNextButton();
  }

  /**
   * Qadam tugagach chaqiriladi.
   * topic.js:    stepDone('topic')
   * quiz.js:     stepDone('quiz')
   * practice.js: stepDone('practice')
   */
  function stepDone(stepId) {
    const current = _steps[_index];

    // Joriy qadam id si mos kelishini tekshiramiz (ixtiyoriy, debug uchun)
    if (current && current.id !== stepId) {
      console.warn(`FlowManager: kutilgan "${current.id}", kelgan "${stepId}"`);
    }

    _showNextButton();
  }

  // ── Private ─────────────────────────────────
  function _showNextButton() {
    _index++;

    if (_index >= _steps.length) {
      // Oqim tugadi
      return;
    }

    const step = _steps[_index];

    // Tugma nomidan oldin bot xabar (ixtiyoriy)
    _showPromptMessage(step, () => {
      createButton(step.label, `flow-btn-${step.id}`);
    });
  }

  function _showPromptMessage(step, callback) {
    // Har bir qadam oldidan ko'rsatiladigan yo'naltiruvchi matn
    const prompts = {
      quiz:     "Pastdagi 👇 tugmani bossangiz <strong>Mavzuga oid testlar</strong> taqdim etiladi",
      practice: "Pastdagi 👇 tugmani bossangiz <strong>Amaliy topshiriqlar</strong> taqdim etiladi",
      end:      "Tabriklaymiz 🎉🎉🎉. Siz darsni to'liq tugatdingiz. Pastdagi 👇 tugmani bosib keyingi darsga o'tishingiz mumkin",
    };

    const text = prompts[step.id];
    if (!text) {
      callback?.();
      return;
    }

    const div = createBotMessageContainer();
    div.classList.add('bot');
    typeText(div, text, callback);
  }

  /**
   * Tugma bosilganda app.js bu funksiyani chaqiradi.
   * Tugmani o'chiradi va tegishli action ni ishga tushiradi.
   * @param {string} btnId  — "flow-btn-topic" kabi id
   */
  function handleButton(btnId) {
    const stepId = btnId.replace('flow-btn-', '');
    const step   = _steps.find((s) => s.id === stepId);

    if (!step) {
      console.warn('FlowManager: step topilmadi:', stepId);
      return;
    }

    if (step.removeBtn) {
      document.getElementById(btnId)?.closest('.center-btn')?.remove();
    }

    // Action funksiyasini chaqirish (global scope dan)
    const fn = window[step.action];
    if (typeof fn === 'function') {
      addUserMessage(step.label);
      fn();
    } else {
      console.error(`FlowManager: "${step.action}" funksiyasi topilmadi`);
    }
  }

  return { init, stepDone, handleButton };
})();