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

// FlowManager
const FlowManager = (() => {
  let _steps    = [];
  let _index    = -1;
  let _flowName = '';

  /**
   * @param {string} flowName 
   * @param {number} startFrom - Boshlang'ich qadam (0, 1, 2...)
   * @param {boolean} isPending - Agar true bo'lsa, joriy qadam tugmachasini chiqarmaydi
   */
  function init(flowName = 'lesson', startFrom = 0, isPending = false) {
    _flowName = flowName;
    _steps    = FLOWS[flowName] ?? FLOWS.lesson;
    _index    = startFrom; 

    // Agar jarayon tugallanmagan (isPending) bo'lsa, 
    // keyingi bosqich tugmasini chiqarmaymiz, foydalanuvchi amalini kutamiz.
    if (!isPending) {
      _showNextButton(true); // true - joriy indeksdagi tugmani ko'rsatish
    }
  }

  function stepDone(stepId) {
    const current = _steps[_index];
    if (current && current.id !== stepId) {
      console.warn(`FlowManager: kutilgan "${current.id}", kelgan "${stepId}"`);
    }
    _showNextButton(false); // Keyingi qadamga o'tish
  }

  function _showNextButton(showCurrent = false) {
    if (!showCurrent) _index++; 

    if (_index >= _steps.length) return;

    const step = _steps[_index];
    _showPromptMessage(step, () => {
      createButton(step.label, `flow-btn-${step.id}`);
    });
  }

  function _showPromptMessage(step, callback) {
    const prompts = {
      quiz:     "Pastdagi 👇 tugmani bossangiz <strong>Mavzuga oid testlar</strong> taqdim etiladi",
      practice: "Pastdagi 👇 tugmani bossangiz <strong>Amaliy topshiriqlar</strong> taqdim etiladi",
      end:      "Tabriklaymiz 🎉🎉🎉. Siz darsni to'liq tugatdingiz. Pastdagi 👇 tugmani bosib keyingi darsga o'tishingiz mumkin",
    };

    const text = prompts[step.id];
    if (!text) { callback?.(); return; }

    const div = createBotMessageContainer();
    div.classList.add('bot');
    typeText(div, text, callback);
  }

  function handleButton(btnId) {
    const stepId = btnId.replace('flow-btn-', '');
    const step   = _steps.find((s) => s.id === stepId);
    if (!step) return;

    if (step.removeBtn) {
      document.getElementById(btnId)?.closest('.center-btn')?.remove();
    }

    const fn = window[step.action];
    if (typeof fn === 'function') {
      addUserMessage(step.label);
      fn();
    }
  }

  return { init, stepDone, handleButton };
})();