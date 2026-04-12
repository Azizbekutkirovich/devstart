<?php
	use yii\widgets\ActiveForm;
	use yii\helpers\Url;
	use yii\helpers\Html;
?>
<h2 style="color: black;">Ro'yxatdan o'tish</h2>
<div class="social-login">
	<button class="google" id="google-register-btn">
		<img src="<?=Url::base()?>/images/google.png" alt="Google logo" />
	  	Google akkaunt orqali
	</button>
</div>

<div class="divider"><span>yoki</span></div>
<?php
	$form = ActiveForm::begin([
	    'id' => 'registerForm',
	    'action' => Url::to(['auth/register']),
	    'enableClientValidation' => true,
	    'enableAjaxValidation' => false
	]);
?>
<?= $form->field($model, 'fullname')->textInput(['placeholder' => "Ism va Familya"])->label(false); ?>
<?= $form->field($model, 'email')->textInput(['placeholder' => 'Email'])->label(false); ?>
<p style='color: blue;'>Parol kamida 8ta belgidan iborat bo'lishi kerak!</p>
<?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Parol'])->label(false); ?>
<?= Html::submitButton("Ro'yxatdan o'tish"); ?>
<?php ActiveForm::end(); ?>
<?php if (Yii::$app->session->hasFlash("google-register-errors")): ?>
	<script type="text/javascript">
		let google_errors = <?=json_encode(Yii::$app->session->getFlash("google-register-errors"));?>
	</script>
	<?php
		$this->registerJs(<<<JS
		$('#registerModal').fadeIn(300).css('display', 'flex');
		Object.values(google_errors).flat().forEach(msg => {
		    showToast(msg, 'error', 10000);
		});
		JS
		);
	?>
<?php endif; ?>