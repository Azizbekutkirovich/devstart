<?php

use yii\helpers\Url;
use yii\web\View;

$this->title = "DevStart";
$this->registerCssFile("https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css");
$this->registerCssFile(Url::base()."/css/start.css");
$this->registerCssFile(Url::base()."/css/registerForm.css");
?>
<header>
	<div></div>
	<nav>
	  <ul>
	    <li><span style="color: black">Avval ro'yxatdan o'tgan bo'lsangiz</span> <a href="<?=Url::to(['auth/login'])?>" class="login-btn">🔑 Kirish</a></li>
	  </ul>
	</nav>
</header>

<div class="container fade" id="container">
	<div class="steps">
	  <div class="step active"></div>
	  <div class="step"></div>
	  <div class="step"></div>
	  <div class="step"></div>
	</div>

	<div id="content" class="fade">
	  <h2>Devstart platformasiga xush kelibsiz! 🚀</h2>
	  <p style="font-size: 18px;">Sun'iy intellekt 🤖 dasturlash o'qituvchingiz bilan tugma bosish orqali dasturlashni o‘rganing</p>
	  <button id="startBtn">Boshlash</button>
	</div>
	<!-- Register Modal -->
	<div id="registerModal" class="modal">
		<div class="modal-content" id="modal-content">
  			<span class="close">&times;</span>
  			<div id="modal-body">
  				<?= $this->render('register', ['model' => $registerModel]); ?>
  			</div>
  		</div>
	</div>
</div>
<?php
	$this->registerJs(
	    "const data = " . json_encode($data) . ";
	     const levels = " . json_encode($levels) . ";",
	    \yii\web\View::POS_HEAD
	);
	$this->registerJsFile("https://cdn.jsdelivr.net/npm/toastify-js");
	$this->registerJsFile(Url::base()."/js/start.js",
	[
        'depends' => [\yii\web\JqueryAsset::class],
        'position' => View::POS_END,
    ]);
    $this->registerJsFile(Url::base()."/js/registerForm.js",
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