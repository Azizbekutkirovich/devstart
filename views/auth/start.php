<?php

use yii\helpers\Url;
use yii\web\View;

$this->title = "Kursni boshlash";
$this->registerCssFile("https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css");
$this->registerCssFile(Url::base()."/css/auth/start.css");
$this->registerCssFile(Url::base()."/css/auth/register.css");
?>
<main class="start-page">
  <div class="start-wrapper">

    <div class="course-header">
      <div class="course-badge">
        <?=$course_name?>
      </div>
      <h1 class="course-title">Kursni <span>boshlash</span></h1>
      <p class="course-desc">Bir necha qadam orqali o'zingizga mos daraja va yo'nalishni tanlang</p>
    </div>

    <div class="progress-card">

      <div class="steps-indicator">
        <div class="step-dot active" id="dot-1"><span class="step-num">1</span></div>
        <div class="step-connector" id="conn-1"></div>
        <div class="step-dot" id="dot-2"><span class="step-num">2</span></div>
      </div>
      <div class="steps-labels">
        <div class="step-label active" id="lbl-1">Daraja tanlash</div>
        <div class="step-label" id="lbl-2">Boshlash usuli</div>
      </div>

      <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="progressBar"></div>
      </div>

      <div class="step-panel active" id="step1">
        <h2 class="step-heading">Kursni qanday darajada o'tilishini xohlaysiz?</h2>
        <p class="step-sub">Bilim darajangizga mos bosqichni belgilang</p>

        <div class="level-grid">
          <?php
          	$i = 1;
          ?>	
          <?php foreach ($levels as $level): ?>
			    <div class="level-card level-<?=$i?>" onclick="selectLevel(this,'<?=$level['id']?>')">
				    <img style="width: 50px; height: 50px;" src="<?=Url::base()?>/images/icons/<?=$level['icon']?>">
				    <div class="level-name"><?=$level['title']?></div>
				    <div class="level-desc"><?=$level['description']?></div>
			    </div>
          <?php $i++; ?>
      	  <?php endforeach; ?>
        </div>

        <div class="step-nav">
          <span style="font-family:'Space Mono',monospace;font-size:.72rem;color:var(--text-muted)">1 / 2</span>
          <button class="btn-primary" id="nextBtn" onclick="goStep2()" disabled>
            Davom etish
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>

      <div class="step-panel" id="step2">
        <h2 class="step-heading">Qanday boshlashni xohlaysiz?</h2>
        <p class="step-sub">Sinab ko'ring yoki to'liq imkoniyatlardan foydalaning</p>

        <div class="action-grid">
          <a id="try-btn" class="action-card try">
            <div class="action-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
            <div>
              <div class="action-title">Sinab ko'rish</div>
              <div class="action-desc">Cheklangan imkoniyatlar faqat sinab ko'rish uchun</div>
            </div>
          </a>
          <a href="#" class="action-card register" onclick="openModal(event)">
            <div class="action-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <div>
              <div class="action-title">Ro'yxatdan o'tish</div>
              <div class="action-desc">To'liq imkoniyatlar: Darslar, o'rganish statistikasi</div>
            </div>
          </a>
        </div>

        <div class="step-nav">
          <button class="btn-ghost" onclick="goStep1()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            Orqaga
          </button>
          <span style="font-family:'Space Mono',monospace;font-size:.72rem;color:var(--text-muted)">2 / 2</span>
        </div>
      </div>
    </div>
  </div>
</main>

<?= $this->render("register", ['model' => $model]) ?>
<script type="text/javascript">
  const urlParams = new URLSearchParams(window.location.search);
  let course_id = urlParams.get('course_id');
</script>
<?php
	$this->registerJsFile("https://cdn.jsdelivr.net/npm/toastify-js");
	$this->registerJsFile(Url::base()."/js/auth/start.js",
	[
        'depends' => [\yii\web\JqueryAsset::class],
        'position' => View::POS_END,
    ]);
    $this->registerJsFile(Url::base()."/js/auth/register.js",
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