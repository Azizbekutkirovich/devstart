<?php
  use yii\helpers\Url;
  $this->title = "Kurslar";
  $this->registerCssFile(Url::base()."/css/site/courses.css");
?>
<div class="page-header">
  <div class="page-header-orb"></div>
  <div class="page-header-inner">
    <div class="page-breadcrumb">
      <a href="<?=Url::to(['site/home'])?>">Bosh sahifa</a>
      <span>›</span>
      <span>Kurslar</span>
    </div>
    <div class="page-header-tag">Ta'lim dasturi</div>
    <h1>Barcha kurslar</h1>
    <p>AI tomonidan moslashtirilgan dasturlash kurslari. Har bir darajaga mos individual yo'l.</p>
  </div>
</div>

<main class="courses-main">
  <div class="courses-grid" id="coursesGrid">
    <?php foreach($courses as $course): ?>
    <div class="course-card reveal">
      <div class="course-thumb">
        <img src="<?=Url::base()?>/images/courses/<?=$course['img']?>" alt="<?=$course['name']?>" onerror="this.style.display='none'">
      </div>
      <div class="course-body">
        <div class="course-title"><?=$course['name']?></div>
        <div class="course-mentor">Mentor: <?=$course['mentor_name']?> (AI)</div>
        <div class="course-desc"><?=$course['title']?></div>
        <div class="course-footer">
          <div class="course-meta">
            <div class="course-meta-item">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?=$course['modules_count']?> ta modul
            </div>
            <div class="course-meta-item">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
              <?=$course['topics_count']?> ta dars
            </div>
          </div>
          <a href="<?=Url::to(['site/course-preview', 'course_id' => $course['id']])?>" class="btn-course">Kursga o'tish →</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</main>