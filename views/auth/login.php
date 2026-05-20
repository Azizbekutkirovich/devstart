<?php
  use yii\widgets\ActiveForm;
  use yii\helpers\Url;
  use yii\web\View;
  use yii\helpers\Html;

  $this->title = "Kirish";
  
  $this->registerCssFile(Url::base()."/css/auth/login.css");
  $this->registerCssFile("https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css");
?>
<main class="login-page">
  <div class="login-container">
    <div class="login-card">
      <div class="login-badge">
        <span class="login-badge-dot"></span>
        // XAVFSIZ LOGIN
      </div>

      <h1 class="login-title">Devstart<span>ga</span> kirish</h1>
      <p class="login-subtitle">Dasturlash olamiga sayohatingizni davom ettiring</p>
      <a href="http://localhost/devstart/auth/google-redirect?operation=login" class="btn-google">
        <svg class="google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
          <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
          <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
          <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Google akkaunt orqali kirish
      </a>

      <div class="divider"><span>yoki</span></div>

      <?php $form = ActiveForm::begin([
          'id' => 'login-form',
          'fieldConfig' => [
              'options' => ['class' => 'form-group'],
              'labelOptions' => ['class' => 'form-label'],
              'inputOptions' => ['class' => 'form-input'],
              'errorOptions' => ['tag' => 'div', 'class' => 'text-danger'],
          ],
      ]); ?>

      <?= $form->field($model, 'email', [
          'template' => "{label}\n<div class='form-input-wrap'>{input}<span class='form-input-icon'><svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><rect x='2' y='4' width='20' height='16' rx='2'/><path d='m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7'/></svg></span></div>\n{error}"
      ])->textInput(['placeholder' => 'sizning@email.com', 'autocomplete' => 'email']) ?>

      <?= $form->field($model, 'password', [
          'template' => "{label}\n<div class='form-input-wrap'>{input}<span class='form-input-icon clickable' id='togglePassword' onclick='togglePwd()'><svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' id='eyeIcon'><path d='M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z'/><circle cx='12' cy='12' r='3'/></svg></span></div>\n{error}"
      ])->passwordInput(['placeholder' => '••••••••', 'id' => 'passwordInput']) ?>

      <button type="submit" class="btn-login">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Kirish
      </button>

      <?php ActiveForm::end(); ?>

      <div class="register-hint">
        <p>Agar ro'yxatdan o'tmagan bo'lsangiz<br><strong>kurslardan birini tanlab ro'yxatdan o'ting</strong></p>
        <a href="<?=Url::to(['site/courses'])?>" class="btn-courses">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          Kurslar
        </a>
      </div>
    </div>
  </div>
</main>
<?php
  $this->registerJsFile("https://cdn.jsdelivr.net/npm/toastify-js");
  $this->registerJsFile(Url::base()."/js/auth/login.js",
  [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => View::POS_END,
  ]);
  $this->registerJsFile(Url::base()."/js/toast-messages.js",
  [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => View::POS_END,
  ]);
?>
<?php if (Yii::$app->session->hasFlash('google-login-error')): ?>
  <script type="text/javascript">
    let msg = "<?=Yii::$app->session->getFlash('google-login-error')?>";
  </script>
  <?php
    $this->registerJs(<<<JS
      showToast(msg, 'error', 10000);
    JS
    );
  ?>
<?php endif; ?>