<?php
  use yii\helpers\Url;
  $this->title = "Profil";
  $this->registerCssFile(Url::base()."/css/dashboard/profile.css");

/*
  view data standart

  fullname
  
  email

  email_verified

*/
?>
<main class="main">
<?php if (!Yii::$app->user->isGuest): ?>
  <div class="page-header">
    <h1>Profil 🧑‍💻</h1>
  </div>

  <div class="profile-grid">
    <div class="pcard full">
      <div class="card-head">
        <div class="card-title">
          👤 Shaxsiy ma'lumotlar
        </div>
      </div>

      <div class="profile-identity">
        <div class="big-avatar"><?=$fullname[0]?></div>
        <div class="ident-info">
          <h2><?=$fullname?></h2>
          <p><?=$email?></p>
        </div>
      </div>

      <div class="info-row">
        <span class="info-key">🪪 Ism va Familya</span>
        <span class="info-val"><?=$fullname?></span>
      </div>
      <div class="info-row">
        <span class="info-key">📧 Email</span>
        <span class="info-val">
          <?=$email?>
          <?php if ($email_verified): ?>          
          <span class="verified-chip">✓ Tasdiqlangan</span>
          <?php else: ?>
          <span class="verified-chip unverified">✕ Tasdiqlanmagan</span>
          <?php endif; ?>
        </span>
      </div>

      <div class="action-row">
        <button class="btn btn-primary" onclick="alert('Bu fuksiya tez orada qo\'shiladi')">✏️ Tahrirlash</button>
        <?php if (!$email_verified): ?>
        <button class="btn btn-verify" onclick="alert('Bu fuksiya tez orada qo\'shiladi')">
          <span>✉️</span> Emailni tasdiqlash
        </button>
        <?php endif; ?>
        <button class="btn btn-danger" onclick="window.location.href='<?=Url::to(['auth/logout'])?>'">🚪 Chiqish</button>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="profile-grid">
    <div class="pcard full">

      <div class="profile-identity">
        <div class="ident-info">
          <h2>Profil yaratish uchun akkauntingizga kiring</h2>
        </div>
        <button class="btn btn-primary" onclick="window.location.href='<?=Url::to(['auth/login'])?>'">Kirish ➡️</button>
      </div>
    </div>
  </div>
<?php endif; ?>
</main>