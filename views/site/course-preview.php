<?php
  use yii\helpers\Url;
  $this->title = "Kurs";
  $this->registerCssFile(Url::base()."/css/site/course-preview.css");
?>
<!-- ═══════════ COURSE HERO ═══════════ -->
<div class="course-hero">
  <div class="course-hero-bg">
    <img src="" alt="" onerror="this.style.display='none'">
  </div>
  <div class="course-hero-overlay"></div>
  <div class="course-hero-content">
    <div class="course-hero-breadcrumb">
      <a href="<?=Url::to(['site/main'])?>">Bosh sahifa</a>
      <span>›</span>
      <a href="<?=Url::to(['site/courses'])?>">Kurslar</a>
      <span>›</span>
      <span><?=$course['name']?></span>
    </div>
    <h1 class="course-hero-title"><?=$course['name']?></h1>
    </div>
  </div>
</div>

<!-- ═══════════ MAIN CONTENT ═══════════ -->
<div class="course-layout">

  <!-- LEFT COLUMN -->
  <div class="course-info-section">

    <!-- About -->
    <div class="info-block reveal">
      <div class="info-block-title">Kurs haqida</div>
      <p class="course-about-text">
        <?=$course['description']?>
      </p>
    </div>

    <!-- What you'll learn -->
    <?php
      $learn_features = json_decode($course['learn_features'], true);
    ?>
    <div class="info-block reveal">
      <div class="info-block-title">Nima o'rganasiz?</div>
      <div class="learn-grid">
        <?php foreach($learn_features as $feature): ?>
        <div class="learn-item">
          <div class="learn-check">✓</div>
          <div class="learn-text"><?=$feature?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Modules -->
    <div class="info-block reveal">
      <div class="info-block-title">Kurs dasturi</div>
      <div class="modules-list">
        <?php $i = 1; ?>
        <?php foreach ($course['modules'] as $module): ?>
        <div class="module-item <?php echo ($i == 1) ? "open": ""; ?>">

          <div class="module-header" onclick="toggleModule(this)">
            <div class="module-number"><?=$i?></div>
            <div class="module-title-wrap">
              <div class="module-title"><?=$module['name']?></div>
              <div class="module-meta"><?=count($module['topics'])?> ta dars</div>
            </div>
            <svg class="module-toggle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
          <div class="module-lessons">
            <?php foreach ($module['topics'] as $topic): ?>
            <a href="#" class="lesson-item">
              <span class="lesson-icon">▶</span>
              <span class="lesson-title"><?=$topic['title']?></span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php $i++ ?>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <!-- RIGHT COLUMN: SIDEBAR -->
  <div class="course-sidebar">

    <div class="sidebar-card reveal">
      <div class="sidebar-thumb">
        <img src="<?=Url::base()?>/images/courses/<?=$course['img']?>" alt="" onerror="this.style.display='none'">
        <div class="sidebar-thumb-overlay"></div>
      </div>
      <div class="sidebar-body">
        <div class="sidebar-price">Bepul</div>
        <div class="sidebar-price-sub">AI bilan o'rganish — hech qanday to'lovsiz</div>
        <a href="<?=Url::to(['auth/start', 'course_id' => $course['id']])?>" class="btn-enroll">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Kursni boshlash
        </a>
        <div class="sidebar-divider"></div>
        <div class="sidebar-features">
          <?php
            $modules_count = count($course['modules']);
            $topics_count = array_sum(
              array_map(fn($m) => count($m['topics']), $course['modules'])
            );
          ?>
          <div class="sidebar-feature"><span class="sf-icon">📚</span><?= $modules_count ?> ta modul, <?= $topics_count ?> ta dars</div>
          <div class="sidebar-feature"><span class="sf-icon">🤖</span>AI mentor bilan interaktiv</div>
          <div class="sidebar-feature"><span class="sf-icon">📝</span>Har mavzuda quiz test</div>
          <div class="sidebar-feature"><span class="sf-icon">🧩</span>Har mavzuda amaliy topshiriq</div>
          <div class="sidebar-feature"><span class="sf-icon">♾️</span>Cheksiz qayta kirish</div>
          <div class="sidebar-feature"><span class="sf-icon">📱</span>Istalgan qurilma orqali</div>
        </div>
      </div>
    </div>

    <div class="mentor-sidebar-card reveal">
      <div class="mentor-sidebar-top">
        <div class="mentor-sidebar-avatar">
          <img src="<?=Url::base()?>/images/mentors/<?=$course['mentor']['chat_img']?>" alt="" onerror="this.style.display='none'">
        </div>
        <div>
          <div class="mentor-sidebar-name"><?=$course['mentor']['name']?></div>
          <div class="mentor-sidebar-role"><?=$course['mentor']['title']?></div>
        </div>
      </div>
      <div class="mentor-sidebar-desc">
        <?=$course['mentor']['description']?>
      </div>
      <div class="mentor-sidebar-skills">
        <?php
          $skills = json_decode($course['mentor']['skills'], true);
        ?>
        <?php foreach ($skills as $skill): ?>
        <span class="skill-pill"><?=$skill['name']?></span>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>
<?php
  $this->registerJsFile(Url::base()."/js/site/course-preview.js");
?>