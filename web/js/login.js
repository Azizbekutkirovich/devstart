$('#google-login-btn').on('click', function () {
    let redirectUrl = 'http://localhost/devstart/auth/google-redirect?operation=login';
    window.location.href = redirectUrl;
});