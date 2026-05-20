<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\SiteAsset;

SiteAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="icon" type="image/png" href="<?=Url::base()?>/images/icons/devstart-logo-to-head.png">
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="grid-bg"></div>

<nav id="mainNav">
  <a href="<?=Url::to(['site/main'])?>" class="nav-logo">
    <img src="<?=Url::base()?>/images/icons/devstart-logo.png" class="navbar-logo"> 
  </a>
  <ul class="nav-links">
    <li><a href="<?=Url::to(['site/main'])?>">Bosh sahifa</a></li>
    <li><a href="<?=Url::to(['site/courses'])?>">Kurslar</a></li>
    <li><a href="<?=Url::to(['site/about-us'])?>">Biz haqimizda</a></li>
  </ul>
  <div class="nav-right">
    <button class="theme-toggle" id="themeToggle" title="Fonni o'zgartirish" aria-label="Toggle theme">
      <span class="ti-icon ti-moon">🌙</span>
      <span class="ti-icon ti-sun">☀️</span>
    </button>
    <?php
      $login_url = (Yii::$app->user->isGuest) ? "auth/login" : "dashboard/home";
    ?>
    <a href="<?=Url::to([$login_url])?>" class="btn-nav-login">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Kirish
    </a>
    <button class="nav-burger" id="navBurger" aria-label="Menyu ochish" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<div class="nav-mobile-menu" id="navMobileMenu" role="navigation" aria-label="Mobil menyu">
  <ul>
    <li><a href="<?=Url::to(['site/main'])?>">Bosh sahifa</a></li>
    <li><a href="<?=Url::to(['site/courses'])?>">Kurslar</a></li>
    <li><a href="<?=Url::to(['site/about-us'])?>">Biz haqimizda</a></li>
  </ul>
  <div class="nav-mobile-bottom">
    <button class="theme-toggle" id="themeToggleMob" title="Fonni o'zgartirish">
      <span class="ti-icon ti-moon">🌙</span>
      <span class="ti-icon ti-sun">☀️</span>
    </button>
  </div>
</div>
<?=$content?>
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-logo">
        <img src="<?=Url::base()?>/images/icons/devstart-logo.png" class="navbar-logo"> 
      </div>
      <div class="footer-tagline">AI bilan dasturlash: Sizga moslashuvchi aqlli ta'lim</div>
      <div class="footer-socials">
        <a href="#" class="social-btn" title="Telegram"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.17 14.37l-2.965-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.983.19z"/></svg></a>
        <a href="#" class="social-btn" title="GitHub"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0 1 12 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg></a>
      </div>
    </div>
    <div class="footer-col">
      <div class="footer-col-title">Platforma</div>
      <ul class="footer-links">
        <li><a href="<?=Url::to(['site/main'])?>">Bosh-sahifa</a></li>
        <li><a href="<?=Url::to(['site/courses'])?>">Kurslar</a></li>
        <li><a href="<?=Url::to(['site/about-us'])?>">Biz haqimizda</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <div class="footer-col-title">Yordam</div>
      <ul class="footer-links">
        <li><a href="https://t.me/anonym_811">Bog'lanish</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <div class="footer-col-title">Huquqiy</div>
      <ul class="footer-links">
        <li><a>Maxfiylik siyosati</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="footer-copy">© 2026 DevStart. Barcha huquqlar himoyalangan.</div>
    <div class="footer-tech">
      <span class="tech-badge">// AI-Powered</span>
      <span class="tech-badge">// O'zbekiston</span>
    </div>
  </div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>