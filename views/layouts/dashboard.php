<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\DashboardAsset;

DashboardAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="logo">
    <div class="logo-icon">🤖</div>
    DevStart
  </div>

  <nav class="nav-section">
    <?php if (!Yii::$app->user->isGuest): ?>
    <a href="<?=Url::to(['dashboard/home'])?>" class="nav-link">
    <?php else: ?>
    <a href="<?=Url::to(['dashboard/home-preview', 'course_id' => Yii::$app->request->get("course_id"), 'level_id' => Yii::$app->request->get("level_id")])?>" class="nav-link">
    <?php endif; ?>
      <div class="icon">🏠</div> Bosh sahifa
    </a>

    <?php if (!Yii::$app->user->isGuest): ?>
    <a href="<?=Url::to(['dashboard/my-courses'])?>" class="nav-link">
    <?php else: ?>
    <a href="<?=Url::to(['dashboard/my-courses-preview', 'course_id' => Yii::$app->request->get("course_id"), 'level_id' => Yii::$app->request->get("level_id")])?>" class="nav-link">
    <?php endif; ?>
      <div class="icon">📚</div> Mening kurslarim
    </a>
    
    <?php if (!Yii::$app->user->isGuest): ?>
    <a href="<?=Url::to(['dashboard/profile'])?>" class="nav-link">
    <?php else: ?>
    <a href="<?=Url::to(['dashboard/profile-preview', 'course_id' => Yii::$app->request->get("course_id"), 'level_id' => Yii::$app->request->get("level_id")])?>" class="nav-link">
    <?php endif; ?>
      <div class="icon">🧑‍💻</div> Profil
    </a>

    <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
      <div class="icon" id="themeIcon">☀️</div>
      <span id="themeLabel">Yorug' fon</span>
    </button>
  </nav>

  <div class="sidebar-footer">
    <div class="user-chip">
      <?php if (!Yii::$app->user->isGuest): ?>
      <div class="avatar"><?=Yii::$app->user->identity->fullname[0]?></div>
      <div class="user-info">
        <div class="user-name"><?=Yii::$app->user->identity->fullname?></div>
      </div>
      <?php else: ?>
        <div class="avatar">M</div>
        <div class="user-info">
          <div class="user-name">Mehmon</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</aside>

<!-- MOBILE TOPBAR -->
<div class="mobile-topbar">
  <div class="mobile-logo">
    <div class="logo-icon">🤖</div>
    DevStart
  </div>
  <button class="burger" id="burger" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</div>
<div class="overlay" id="overlay"></div>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>