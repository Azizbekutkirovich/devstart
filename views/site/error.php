<?php


use yii\helpers\Url;

$this->title = "Xatolik";
$this->registerCssFile(Url::base()."/css/site/error.css");
?>
<main class="error-wrapper">
  <div class="error-content">
    <div class="error-code">404</div>
    <h1 class="error-title">Sahifa topilmadi</h1>
    <p class="error-desc">
        Kechirasiz, siz qidirayotgan sahifa mavjud emas yoki boshqa manzilga ko'chirilgan bo'lishi mumkin.
    </p>
    <div class="error-actions">
      <a href="<?=Url::to(['site/main']);?>" class="btn-nav-login">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 5px;">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          </svg>
          Bosh sahifaga qaytish
      </a>
      <a href="#" onclick="history.back(); return false;" class="btn-secondary">Orqaga</a>
    </div>
  </div>
</main>