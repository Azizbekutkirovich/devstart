<?php

use yii\helpers\Url;
use yii\web\View;

$this->title = "Kursni boshlash";
$this->registerCssFile("https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css");
$this->registerCssFile(Url::base()."/css/auth/start-user.css");
/*
	view data standart:

	course_name

	fullname

	levels = [
		[
			'title'
			'description'
			'icon'
		],
		...
	]
*/
?>
<!-- MAIN -->
<main class="start-page">
  <div class="start-wrapper">

    <!-- Course Header -->
    <div class="course-header">
      <div style="display:flex;align-items:center;gap:0;flex-wrap:wrap;">
        <div class="course-badge">
          <?= $course_name ?>
        </div>
        <div class="user-greeting-badge">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Xush kelibsiz, <?=$fullname?>!
        </div>
      </div>
      <h1 class="course-title">Darajani <span>tanlang</span></h1>
      <p class="course-desc">Bilim darajangizga mos bosqichni belgilang va kursni boshlang</p>
    </div>

    <!-- Progress Card -->
    <div class="progress-card">

      <!-- Single step indicator -->
      <div class="single-step-indicator" id="stepIndicator">
        <div class="single-step-dot" id="stepDot">1</div>
        <div class="single-step-info">
          <div class="single-step-label" id="stepLabel">Daraja tanlash</div>
          <div class="single-step-hint" id="stepHint">Quyidagi darajalardan birini tanlang</div>
        </div>
        <svg id="stepCheckIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent4)" stroke-width="2.5" style="display:none;flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>
      </div>

      <!-- Progress bar -->
      <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="progressBar"></div>
      </div>

      <!-- ── STEP 1 (only step) ── -->
      <div class="step-panel active" id="step1">
        <h2 class="step-heading">Darajangizni tanlang</h2>
        <p class="step-sub">Bilim darajangizga mos bosqichni belgilang</p>
        <div class="level-grid">
          <?php
          	$i = 1;
          ?>	
          <?php foreach ($levels as $level): ?>
			    <div class="level-card level-<?=$i?>" onclick="selectLevel(this,'<?=$level['id']?>')">
			    	<div class="check-badge">✓</div>
				    <img style="width: 50px; height: 50px;" src="<?=Url::base()?>/images/icons/<?=$level['icon']?>">
				    <div class="level-name"><?=$level['title']?></div>
				    <div class="level-desc"><?=$level['description']?></div>
			    </div>
          <?php $i++; ?>
      	  <?php endforeach; ?>
        </div>

        <div class="step-nav">
          <button class="btn-launch" id="startBtn" onclick="startCourse()" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            Kursni boshlash
          </button>
        </div>
      </div>

    </div>
  </div>
</main>
<?php
	$this->registerJsFile(Url::base()."/js/auth/start-user.js",
	[
        'depends' => [\yii\web\JqueryAsset::class],
        'position' => View::POS_END,
    ]);
?>