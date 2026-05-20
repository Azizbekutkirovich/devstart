const _history  = window.__CHAT_HISTORY__;
const _flowName = document.querySelector('meta[name="topic-flow"]')?.getAttribute('content') || 'default';

if (_history?.length) {
  restoreChat(_history);
}

const _inferred = _history?.length
  ? inferStepFromHistory(_history)
  : { step: 0, continueTopic: false, practiceInputs: false, isPending: false };

if (window.__USER_ROLE__ == 'guest') {
  FlowManager.init(_flowName, 0, false);
} else {
  FlowManager.init(_flowName, _inferred.step, _inferred.isPending);
}


if (_inferred.continueTopic) {
  createButton('Davom etish ➡', 'continue-topic');
}

if (_inferred.practiceInputs) {
  restorePracticeInputs();
}

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