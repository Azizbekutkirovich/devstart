<?php
  use yii\helpers\Url;
  $this->title = "Profil";
  $this->registerCssFile(Url::base()."/css/profile.css");
?>
<div class="container">
  <?php if (!Yii::$app->user->isGuest): ?>
  
  <?php else: ?>
  <div class="profile-card" style="text-align: center; margin-top: 120px;">
    <h2 class="card-title">👤 Profil yaratish uchun akkauntingizga kiring</h2>
    <a class="verify-btn" href="<?=Url::to(['auth/login'])?>" style="text-decoration: none;">Kirish ➡️</a>    
  </div>
  <?php endif; ?>
</div>