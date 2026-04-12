/**
 * Universal toast message function with Toastify JS
 * @param {string} message - Ko'rsatiladigan xabar
 * @param {string} type - 'success', 'error', 'warning', 'info'
 * @param {number} duration - Toast ko'rsatilish vaqti (ms), default 3000
 */
function showToast(message, type = 'success', duration = 3000) {
    // Toast konfiguratsiyasi
    const config = {
        success: {
            gradient: 'linear-gradient(135deg, #28a745, #5cd68d)',
            icon: '✓',
            title: 'Muvaffaqiyatli!'
        },
        error: {
            gradient: 'linear-gradient(135deg, #dc3545, #ff6b6b)',
            icon: '✕',
            title: 'Xatolik!'
        },
        warning: {
            gradient: 'linear-gradient(135deg, #ffc107, #ffdd57)',
            icon: '⚠',
            title: 'Ogohlantirish!'
        },
        info: {
            gradient: 'linear-gradient(135deg, #17a2b8, #5dccf5)',
            icon: 'ℹ',
            title: 'Ma\'lumot'
        }
    };

    // Agar bunday tip bo'lmasa, default success
    const toastConfig = config[type] || config.success;

    // Toast mazmunini formatlash
    const toastHTML = `
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="font-size: 24px; font-weight: bold;">${toastConfig.icon}</div>
            <div>
                <strong style="display: block; margin-bottom: 4px;">${toastConfig.title}</strong>
                <div>${message}</div>
            </div>
        </div>
    `;

    // Toastify ni ko'rsatish
    Toastify({
        text: toastHTML,
        duration: duration,
        gravity: "top",
        position: "right",
        escapeMarkup: false, // HTML ishlatish uchun
        stopOnFocus: true,
        style: {
            background: toastConfig.gradient,
            color: '#fff',
            padding: '18px 22px',
            borderRadius: '14px',
            boxShadow: '0 15px 35px rgba(0, 0, 0, .2)',
            fontSize: '16px',
            minWidth: '300px',
            maxWidth: '450px',
            fontFamily: 'Arial, sans-serif'
        },
        offset: {
            x: 30,
            y: 30
        }
    }).showToast();
}

/**
 * Register error handler uchun maxsus funksiya
 * @param {Object} xhr - jQuery AJAX xhr objekti
 * @param {string} status - Status matni
 * @param {string} error - Xatolik matni
 */
function handleRegisterError(xhr, status, error) {
    let errorMessage = '';
    
    switch(xhr.status) {
        case 0:
            errorMessage = 'Internet bilan bog\'lanishda xatolik.<br>Iltimos, internetni tekshiring.';
            break;
        case 400:
            errorMessage = 'Noto\'g\'ri so\'rov yuborildi';
            break;
        case 401:
            errorMessage = 'Tizimga kirishingiz kerak';
            setTimeout(() => window.location.href = '/login', 2000);
            break;
        case 403:
            errorMessage = 'Sizda bu amalni bajarish huquqi yo\'q';
            break;
        case 404:
            errorMessage = 'So\'ralgan ma\'lumot topilmadi';
            break;
        case 422:
            errorMessage = 'Ma\'lumotlarni to\'g\'ri kiriting';
            // Agar server validation errors qaytarsa
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                const errorList = Object.values(errors).flat().join('<br>');
                errorMessage = errorList;
            }
            break;
        case 429:
            errorMessage = 'Juda ko\'p so\'rov yubordingiz.<br>Iltimos, biroz kuting!';
            break;
        case 500:
            errorMessage = 'Server xatosi yuz berdi.<br>Iltimos, qayta urinib ko\'ring!';
            break;
        case 503:
            errorMessage = 'Xizmat vaqtincha ishlamayapti.<br>Iltimos, keyinroq urinib ko\'ring!';
            break;
        default:
            errorMessage = "Noma'lum xatolik yuz berdi.<br>Iltimos, keyinroq urinib ko'ring!";
    }
    
    showToast(errorMessage, 'error', 4000);
}