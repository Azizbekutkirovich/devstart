<?php
  use yii\widgets\ActiveForm;
  use yii\helpers\Url;
  use yii\web\View;
  use yii\helpers\Html;

  $this->title = "Kirish";
  
  $this->registerCssFile(Url::base()."/css/login.css");
?>
<div class="container">
  <div class="login-content">
    <h2 style="color: black;"><span class="logo">🤖DevStart</span>ga kirish</h2>

    <?php if (Yii::$app->session->hasFlash("google-login-error")): ?>
    <div class="error-box">
        <h3><?=Yii::$app->session->getFlash("google-login-error")?></li>
    </div>
    <?php endif; ?>

    <div class="social-login">
      <button class="google" id="google-login-btn">
        <img src="<?=Url::base()?>/images/google.png" alt="Google logo" />
        Google akkaunt orqali
      </button>
    </div>

    <div class="divider"><span>yoki</span></div>
    <?php $form = ActiveForm::begin([
      'id' => 'registerForm',
      'enableClientValidation' => true
    ]); ?>
      <?= $form->field($model, "email")->textInput(['placeholder' => 'Email'])->label(false); ?>
      <?= $form->field($model, "password")->passwordInput(["placeholder" => 'Parol'])->label(false); ?>
      <?= Html::submitButton("Kirish"); ?>
    <?php ActiveForm::end(); ?>
    <p>Agar ro'yxatdan o'tmagan bo'lsangiz <br> <a href="<?=Url::to(['auth/start'])?>">Ro'yxatdan o'tish</a></p>
  </div>
</div>
<?php
  $this->registerJsFile(Url::base()."/js/login.js",
  [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => View::POS_END,
  ]);
?>