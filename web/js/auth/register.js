$(document).on('submit', '#registerForm', function(e) {
    e.preventDefault();
    
    
    var form = $(this);
    var submitBtn = form.find('[type="submit"]');

    submitBtn.prop('disabled', true).html('<span class="loader"></span> Yuklanmoqda...');
    
    var formData = form.serialize();
    formData += '&SelectedForm[course_id]=' + encodeURIComponent(course_id);
    formData += '&SelectedForm[level_id]=' + encodeURIComponent(selectedLevelId);
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        dataType: 'json',
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(res) {
            if (res.success) {
                showToast("Ro'yxatdan o'tish yakunlandi", 'success');
                setTimeout(() => window.location.href = '/devstart/dashboard/home', 1500);
            } else {
                if (res.errors) {
                    Object.values(res.errors).flat().forEach(msg => {
                        showToast(msg, 'error', 5000);
                    });
                }

                if (res.html) {
                    $('#modal-body').html(res.html);
                }
            }
        },
        complete: function() {
            // Loading holatini o'chirish
            submitBtn.prop('disabled', false).html('Ro\'yxatdan o\'tish');
        },
        error: handleRegisterError
    });
});

$('#google-register-btn').on('click', function () {
    const selected = {
      course_id: course_id,
      level_id: selectedLevelId
    };
    let redirectUrl = 'http://localhost/devstart/auth/google-redirect?operation=register&selected=' + encodeURIComponent(JSON.stringify(selected));
    window.location.href = redirectUrl;
});