<?php
	use yii\widgets\ActiveForm;
	use yii\helpers\Url;
	use yii\helpers\Html;
?>
<div class="modal-overlay" id="modalOverlay" onclick="closeModalOutside(event)">
  <div class="modal" id="modalBox">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <div class="modal-badge">// YANGI AKKAUNT</div>
    <h2 class="modal-title">Ro'yxatdan <span>o'tish</span></h2>
    <p class="modal-sub">Barcha imkoniyatlardan foydalaning</p>

    <a id="google-register-btn" style="width:100%;padding:12px 20px;background:var(--surface2);border:1px solid var(--border);border-radius:12px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.88rem;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:all .25s;text-decoration:none;margin-bottom:18px;" onmouseover="this.style.background='rgba(255,255,255,.05)'" onmouseout="this.style.background='var(--surface2)'">
      <svg width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
      Google orqali ro'yxatdan o'tish
    </a>

    <div style="display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:10px;margin-bottom:18px;">
      <div style="height:1px;background:var(--border)"></div>
      <span style="font-family:'Space Mono',monospace;font-size:.7rem;color:var(--text-muted);letter-spacing:.04em">yoki</span>
      <div style="height:1px;background:var(--border)"></div>
    </div>

<?php
    $form = ActiveForm::begin([
	    'id' => 'registerForm',
	    'action' => Url::to(['auth/register']),
	    'enableClientValidation' => true,
	    'enableAjaxValidation' => false,
	    'fieldConfig' => [
	        'options' => ['class' => 'modal-form-group'],
	        'labelOptions' => ['class' => 'modal-label'],
	        'inputOptions' => ['class' => 'modal-input'],
	        'errorOptions' => ['class' => 'text-danger'],
	    ],
	]);
?>

<?= $form->field($model, 'fullname', [
    'template' => "{label}\n{input}\n{error}",
    'options' => ['class' => 'modal-form-group', 'style' => 'flex: 1;']
])->textInput(['placeholder' => "Ism va Familiya"])->label("ISM VA FAMILIYA"); ?>

<?= $form->field($model, 'email')->textInput(['placeholder' => 'sizning@email.com'])->label("EMAIL"); ?>

<?= $form->field($model, 'password', [
    'template' => "{label}\n{input}\n<p style='color: blue; font-size: 12px; margin-top: 4px;'>Parol kamida 8 ta belgidan iborat bo'lishi kerak!</p>\n{error}"
])->passwordInput(['placeholder' => '••••••••'])->label("PAROL"); ?>

<button type="submit" class="btn-modal-submit">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
        <line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
    </svg>
    Ro'yxatdan o'tish
</button>

<?php ActiveForm::end(); ?>
    <p class="modal-login-hint">Akkauntingiz bormi? <a href="<?=Url::to(['auth/login'])?>">Kirish</a></p>
  </div>
</div>
<?php if (Yii::$app->session->hasFlash("google-register-errors")): ?>
    <script type="text/javascript">
        window.google_errors = <?= json_encode(Yii::$app->session->getFlash("google-register-errors")); ?>;
    </script>
    <?php
    $this->registerJs(<<<JS
        $('#registerModal').fadeIn(300).css('display', 'flex');
        if (typeof google_errors !== 'undefined') {
            Object.values(google_errors).flat().forEach(msg => {
                if (typeof showToast === 'function') {
                    showToast(msg, 'error', 10000);
                } else {
                    console.error("showToast funksiyasi topilmadi!");
                }
            });
        }
JS
    );
    ?>
<?php endif; ?>