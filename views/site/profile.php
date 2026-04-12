<?php
  use yii\helpers\Url;
  $this->title = "Profil";
  $this->registerCssFile(Url::base()."/css/profile.css");
?>
<div class="container">
  <?php if (!Yii::$app->user->isGuest): ?>
  <div class="profile-card">
    <h2 class="card-title">👤 Sizning ma'lumotlaringiz</h2>

    <p class="profile-item"><strong>Ism familya:</strong> Safarov Azamat</p>
    <p class="profile-item"><strong>Email:</strong> azamat@gmail.com <button class="verify-btn">📧 Tasdiqlash ✔️</button></p>

    <button class="logout-btn">🚪 Akkauntdan chiqish</button>
  </div>

  <div class="progress-card">
    <h2 class="card-title">📊 O'rganish statistikasi</h2>
    <div class="progress-item">
      <p>PHP (Backend) — 60%</p>
      <div class="progress-bar">
        <div class="progress-fill w-60"></div>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div class="profile-card" style="text-align: center; margin-top: 120px;">
    <h2 class="card-title">👤 Profil yaratish uchun tizimga kiring</h2>
    <a class="verify-btn" href="<?=Url::to(['auth/login'])?>" style="text-decoration: none;">Kirish ➡️</a>    
  </div>
  <?php endif; ?>
</div>