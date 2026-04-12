<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);
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

<nav class="navbar">
    <div class="logo">🤖DevStart</div>

    <div class="nav-links">
      <?php if (!Yii::$app->user->isGuest): ?>
      <a href="<?=Url::to(['site/home'])?>" id="home">🏠 Bosh sahifa</a>
      <?php else: ?>
      <a href="" id="home">🏠 Bosh sahifa</a>
      <?php endif; ?>
      <a href="<?=Url::to(['site/profile'])?>" id="profile">🧑‍💻 Profil</a>
      <a href="<?=Url::to(['site/about-us'])?>" id="about-us">ℹ️ Biz haqimizda</a>
    </div>

    <div class="burger" id="burger">
      <div></div>
      <div></div>
      <div></div>
    </div>
  </nav>

  <div class="side-menu" id="sideMenu">
    <?php if (!Yii::$app->user->isGuest): ?>
    <a href="<?=Url::to(['site/home'])?>" id="home">🏠 Bosh sahifa</a>
    <?php else: ?>
    <a href="" id="home">🏠 Bosh sahifa</a>
    <?php endif; ?>
    <a href="<?=Url::to(['site/profile'])?>" id="profile">🧑‍💻 Profil</a>
    <a href="<?=Url::to(['site/about-us'])?>" id="about-us">ℹ️ Biz haqimizda</a>
  </div>

  <div class="overlay" id="overlay"></div>

  <?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>