window.selected = JSON.parse(localStorage.getItem('userSelections')) || { 
    category_id: "", 
    language_id: "", 
    level_id: "",
    course_id: ""
};

let stepIndex = parseInt(localStorage.getItem('currentStep')) || 0;

// Sahifa yuklanganida
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const authError = urlParams.get('error');
    
    if (authError) {
        const errorMessage = urlParams.get('message');
        showErrorMessage(errorMessage || 'Google orqali kirish jarayonida xatolik yuz berdi');
        const newUrl = window.location.pathname + (stepIndex > 0 ? `?step=${stepIndex}` : '');
        window.history.replaceState({ step: stepIndex }, '', newUrl);
    }
    
    restoreState();
});

// Browser back/forward
window.onpopstate = function(event) {
    if (event.state !== null && event.state.step !== undefined) {
        const targetStep = event.state.step;
        stepIndex = targetStep;
        localStorage.setItem('currentStep', stepIndex.toString());
        
        if (stepIndex === 0) {
            window.selected = { category_id: "", language_id: "", level_id: "", course_id: "" };
        } else if (stepIndex === 1) {
            window.selected.language_id = "";
            window.selected.level_id = "";
            window.selected.course_id = "";
        } else if (stepIndex === 2) {
            window.selected.level_id = "";
        }
        localStorage.setItem('userSelections', JSON.stringify(window.selected));
        
        updateSteps();
        navigateToStep(stepIndex, true);
    }
};

function restoreState() {
    if (stepIndex > 0) {
        if (!window.history.state || window.history.state.step !== stepIndex) {
            window.history.replaceState({ step: stepIndex }, '', `?step=${stepIndex}`);
        }
        updateSteps();
        navigateToStep(stepIndex, true);
    }
}

function navigateToStep(step, isRestoring = false) {
    switch(step) {
        case 0: break;
        case 1: showCategorySelection(isRestoring); break;
        case 2: showLanguages(isRestoring); break;
        case 3: showLevels(isRestoring); break;
        case 4: showLogin(isRestoring); break;
    }
}

function updateSteps() {
    $('.step').removeClass('active');
    for (let i = 0; i <= stepIndex; i++) {
        $('.step').eq(i).addClass('active');
    }
}

function showErrorMessage(message) {
    const errorDiv = $('<div class="error-message"></div>').css({
        background: '#ff4444',
        color: 'white',
        padding: '15px',
        borderRadius: '8px',
        marginBottom: '20px',
        textAlign: 'center',
        animation: 'slideDown 0.3s ease'
    });
    errorDiv.text(message);
    $('#content').prepend(errorDiv);
    setTimeout(() => errorDiv.fadeOut(300, function() { $(this).remove(); }), 5000);
}

// Start button
$(document).on('click', '#startBtn', function() {
    stepIndex = 1;
    localStorage.setItem('currentStep', stepIndex.toString());
    updateSteps();
    window.history.pushState({ step: 1 }, '', '?step=1');
    showCategorySelection();
});

// Category tanlash
function showCategorySelection(isRestoring = false) {
    let contentHTML = `<h2>Qaysi sohani o'rganishni xohlaysiz?</h2><div class="options">`;
    
    data.forEach(item => {
        const selected = window.selected.category_id == item.category_id ? 'selected' : '';
        contentHTML += `
            <div class="option ${selected}" 
                 data-category-id="${item.category_id}">
                <strong>${item.category}</strong> — ${item.title}
            </div>`;
    });
    
    contentHTML += `</div>
        <div class="button-group">
            <button id="nextBtn" ${!window.selected.category_id ? 'disabled' : ''}>Davom etish</button>
        </div>`;
    
    addContent(contentHTML);
    
    selectOption('category_id', function() {
        goToNextStep(2, showLanguages);
    });
}

// Language tanlash
function showLanguages(isRestoring = false) {
    const categoryData = data.find(item => item.category_id == window.selected.category_id);
    
    if (!categoryData) {
        goToStep(1);
        return;
    }
    
    let contentHTML = `
        <h2><span style="color: red;">${categoryData.category}</span>ni qaysi tilda o'rganmoqchisiz?</h2>
        <div class="options">`;
    
    categoryData.languages.forEach(lang => {
        const selected = window.selected.language_id == lang.language_id ? 'selected' : '';
        contentHTML += `
            <div class="option ${selected}" 
                 data-language-id="${lang.language_id}"
                 data-course-id="${lang.course_id}">
                <strong style="color: yellow;">${lang.language}</strong> — ${lang.title}
            </div>`;
    });
    
    contentHTML += `</div>
        <div class="button-group">
            <button id="backBtn" class="back-btn">⬅ Ortga</button>
            <button id="nextBtn" ${!window.selected.language_id ? 'disabled' : ''}>Davom etish</button>
        </div>`;
    
    addContent(contentHTML);
    
    // Language uchun maxsus selectOption (course_id ham saqlanadi)
    $(document).off('click', '.option').on('click', '.option', function() {
        $('.option').removeClass('selected');
        $(this).addClass('selected');
        window.selected.language_id = $(this).data('language-id');
        window.selected.course_id   = $(this).data('course-id');
        localStorage.setItem('userSelections', JSON.stringify(window.selected));
        $('#nextBtn').prop('disabled', false);
    });
    
    $(document).off('click', '#nextBtn').on('click', '#nextBtn', function() {
        goToNextStep(3, showLevels);
    });
    
    $(document).off('click', '#backBtn').on('click', '#backBtn', function() {
        goToStep(1);
    });
}

// Level tanlash
function showLevels(isRestoring = false) {
    const categoryData = data.find(item => item.category_id == window.selected.category_id);
    
    let optionsHTML = '';
    levels.forEach(level => {
        const selected = window.selected.level_id == level.id ? 'selected' : '';
        optionsHTML += `
            <div class="option ${selected}" 
                 data-level-id="${level.id}">
                <strong>${level.name}</strong>
            </div>`;
    });
    
    addContent(`
        <h2><span style="color: red">${categoryData ? categoryData.category : ''}</span>dagi tajribangiz qanday?</h2>
        <div class="options">${optionsHTML}</div>
        <div class="button-group">
            <button id="backBtn" class="back-btn">⬅ Ortga</button>
            <button id="nextBtn" ${!window.selected.level_id ? 'disabled' : ''}>Davom etish</button>
        </div>
    `);

    selectOption('level_id', function() {
        goToNextStep(4, showLogin);
    });
    
    $(document).off('click', '#backBtn').on('click', '#backBtn', function() {
        goToStep(2);
    });
}

// Login sahifasi
function showLogin(isRestoring = false) {
    addContent(`
        <h2>Eslab qolinishingiz uchun tizimda ro'yxatdan o'ting</h2>
        <p>Agar ro'yxatdan o'tishni istamasangiz, mehmon sifatida kirishingiz mumkin 👇</p>
        <div style="display:flex; flex-direction:column; gap:15px; align-items:center; margin-top:25px;">
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap: wrap;">
                <button id="backBtn" class="back-btn">⬅ Ortga</button>
                <button id="showRegisterBtn">Ro'yxatdan o'tish</button>
                <button class="skip-btn" id="guestBtn">Mehmon sifatida kirish</button>
            </div>
        </div>
    `);
    
    $(document).off('click', '#backBtn').on('click', '#backBtn', function() {
        goToStep(3);
    });
}

// Umumiy selectOption (language bundan tashqari)
function selectOption(type, nextFunc) {
    const dataAttr = {
        category_id: 'category-id',
        level_id: 'level-id',
    };
    
    $(document).off('click', '.option').on('click', '.option', function() {
        $('.option').removeClass('selected');
        $(this).addClass('selected');
        window.selected[type] = $(this).data(dataAttr[type]);
        localStorage.setItem('userSelections', JSON.stringify(window.selected));
        $('#nextBtn').prop('disabled', false);
    });
    
    $(document).off('click', '#nextBtn').on('click', '#nextBtn', nextFunc);
}

function goToNextStep(nextStep, callback) {
    stepIndex = nextStep;
    localStorage.setItem('currentStep', stepIndex.toString());
    updateSteps();
    window.history.pushState({ step: nextStep }, '', `?step=${nextStep}`);
    if (callback) callback(false);
}

function goToStep(targetStep) {
    if (targetStep <= 1) {
        window.selected.language_id = "";
        window.selected.level_id = "";
        window.selected.course_id = "";
    } else if (targetStep === 2) {
        window.selected.level_id = "";
    }
    localStorage.setItem('userSelections', JSON.stringify(window.selected));
    
    stepIndex = targetStep;
    localStorage.setItem('currentStep', stepIndex.toString());
    updateSteps();
    window.history.pushState({ step: targetStep }, '', `?step=${targetStep}`);
    navigateToStep(targetStep, true);
}

function addContent(html) {
    const content = $('#content');
    content.removeClass("fade");
    void content[0].offsetWidth;
    content.html(html);
    content.addClass("fade");
}

function clearState() {
    localStorage.removeItem('userSelections');
    localStorage.removeItem('currentStep');
    window.selected = { category_id: "", language_id: "", level_id: "", course_id: "" };
    stepIndex = 0;
    window.history.replaceState({ step: 0 }, '', window.location.pathname);
}

// Modal
$(document).on('click', '#showRegisterBtn', function() {
    $('#registerModal').fadeIn(300).css('display', 'flex');
});

$(document).on('click', '.close, #registerModal', function(e) {
    if (e.target === this) $('#registerModal').fadeOut(300);
});

$(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#registerModal').is(':visible')) {
        $('#registerModal').fadeOut(300);
    }
});

// Guest button — faqat course_id va level_id
$(document).on('click', '#guestBtn', function() {
    // lokal uchun
    const redirectUrl = "http://localhost/devstart/site/preview"
        + "?course_id=" + encodeURIComponent(window.selected.course_id)
        + "&level_id="  + encodeURIComponent(window.selected.level_id);
    
    // ngrok uchun:
    // const redirectUrl = "https://unendowed-unsurmountable-quintin.ngrok-free.dev/site/preview"
    //     + "?course_id=" + encodeURIComponent(window.selected.course_id)
    //     + "&level_id="  + encodeURIComponent(window.selected.level_id);
    window.location.href = redirectUrl;
});