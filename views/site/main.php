<?php
  use yii\helpers\Url;
  $this->title = "Bosh sahifa";
  $this->registerCssFile(Url::base()."/css/site/main.css");
?>
<section class="hero" id="home">
  <div class="hero-orb orb-1"></div>
  <div class="hero-orb orb-2"></div>
  <div class="hero-orb orb-3"></div>
  <div class="hero-content">
    <div class="hero-eyebrow"><span class="dot-pulse"></span>AI · Sun'iy Intellekt Asosida Ta'lim</div>
    <h2 class="hero-title">
      <span class="line-1">Dasturlashni</span>
      <span class="line-2">AI bilan o'rgan</span>
      <span class="line-3">— to'liq individual.</span>
    </h2>
    <p class="hero-subtitle">AI bilan dasturlash: Sizga moslashuvchi aqlli ta'lim</p>
    <div class="hero-cta">
      <a href="#courses" class="btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        O'qishni boshlash
      </a>
    </div>
  </div>
</section>

<section class="how-section" id="how">
  <div class="section-inner">
    <div class="section-tag">Jarayon</div>
    <h2 class="section-title reveal">DevStart qanday ishlaydi?</h2>
    <p class="section-desc reveal">Platformaga kirganingizdan boshlab, professional malaka egallaguningizcha 3 bosqichli aqlli tizim sizni to'xtovsiz qo'llab-quvvatlaydi</p>
    <div class="how-steps">
      <div class="how-card reveal" style="animation-delay:.1s">
        <div class="how-card-img">
          <span class="how-step-badge">01 — BOSQICH</span>
          <svg width="100" height="100" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="40" r="22" stroke="#00e5ff" stroke-width="2" opacity=".5"/><circle cx="50" cy="40" r="14" fill="rgba(0,229,255,.1)" stroke="#00e5ff" stroke-width="1.5"/><line x1="50" y1="18" x2="50" y2="10" stroke="#00e5ff" stroke-width="1.5" opacity=".4"/><line x1="50" y1="62" x2="50" y2="70" stroke="#00e5ff" stroke-width="1.5" opacity=".4"/><line x1="28" y1="40" x2="20" y2="40" stroke="#00e5ff" stroke-width="1.5" opacity=".4"/><line x1="72" y1="40" x2="80" y2="40" stroke="#00e5ff" stroke-width="1.5" opacity=".4"/><circle cx="50" cy="40" r="5" fill="#00e5ff" opacity=".8"/><path d="M30 75 Q50 65 70 75" stroke="#00e5ff" stroke-width="1.5" fill="none" opacity=".4"/><rect x="35" y="75" width="30" height="12" rx="4" fill="rgba(0,229,255,.08)" stroke="#00e5ff" stroke-width="1" opacity=".6"/><line x1="40" y1="81" x2="60" y2="81" stroke="#00e5ff" stroke-width="1" opacity=".5"/></svg>
        </div>
        <div class="how-card-body"><div class="how-card-title">Darajani tanlash</div><div class="how-card-text">O'zingizga mos kurs o'tilish darajasini tanlaysiz</div></div>
      </div>
      <div class="how-card reveal" style="animation-delay:.2s">
        <div class="how-card-img">
          <span class="how-step-badge">02 — BOSQICH</span>
          <svg width="110" height="100" viewBox="0 0 110 100" fill="none"><rect x="10" y="15" width="90" height="70" rx="8" fill="rgba(123,47,255,.08)" stroke="#7b2fff" stroke-width="1.5" opacity=".6"/><rect x="18" y="28" width="40" height="6" rx="3" fill="#7b2fff" opacity=".4"/><rect x="18" y="40" width="28" height="4" rx="2" fill="#7b2fff" opacity=".25"/><rect x="18" y="50" width="36" height="4" rx="2" fill="#7b2fff" opacity=".25"/><rect x="18" y="60" width="22" height="4" rx="2" fill="#7b2fff" opacity=".25"/><circle cx="82" cy="52" r="16" fill="rgba(123,47,255,.12)" stroke="#7b2fff" stroke-width="1.5" opacity=".7"/><text x="82" y="57" text-anchor="middle" font-size="14" fill="#7b2fff" opacity=".9">AI</text><line x1="60" y1="52" x2="66" y2="52" stroke="#7b2fff" stroke-width="1.5" stroke-dasharray="3,2" opacity=".5"/></svg>
        </div>
        <div class="how-card-body"><div class="how-card-title">AI bilan o'rganish</div><div class="how-card-text">Dars jarayonini to‘liq AI mentor boshqaradi: u sizga interaktiv misollar beradi va mavzuni mukammal o‘zlashtirishingizni ta’minlaydi</div></div>
      </div>
      <div class="how-card reveal" style="animation-delay:.3s">
        <div class="how-card-img">
          <span class="how-step-badge">03 — BOSQICH</span>
          <svg width="110" height="100" viewBox="0 0 110 100" fill="none"><rect x="15" y="60" width="16" height="26" rx="4" fill="rgba(255,107,53,.15)" stroke="#ff6b35" stroke-width="1.5" opacity=".6"/><rect x="37" y="44" width="16" height="42" rx="4" fill="rgba(255,107,53,.2)" stroke="#ff6b35" stroke-width="1.5" opacity=".7"/><rect x="59" y="30" width="16" height="56" rx="4" fill="rgba(255,107,53,.3)" stroke="#ff6b35" stroke-width="1.5" opacity=".8"/><rect x="81" y="18" width="16" height="68" rx="4" fill="rgba(255,107,53,.45)" stroke="#ff6b35" stroke-width="1.5"/><path d="M23 57 L45 41 L67 27 L89 15" stroke="#ff6b35" stroke-width="2" fill="none" stroke-linecap="round" opacity=".8"/><circle cx="23" cy="57" r="3.5" fill="#ff6b35" opacity=".8"/><circle cx="45" cy="41" r="3.5" fill="#ff6b35" opacity=".8"/><circle cx="67" cy="27" r="3.5" fill="#ff6b35" opacity=".8"/><circle cx="89" cy="15" r="3.5" fill="#ff6b35"/></svg>
        </div>
        <div class="how-card-body"><div class="how-card-title">Baholash & O'sish</div><div class="how-card-text">Test va amaliy topshiriqlarni bajarasiz, AI esa ularni bir zumda tekshirib, xatolaringizni tahlil qiladi va sizga individual tavsiyalar beradi</div></div>
      </div>
    </div>
  </div>
</section>

<div class="divider"></div>

<section id="mentors">
  <div class="section-inner">
    <div class="section-tag">Mentorlar</div>
    <h2 class="section-title reveal">AI Mentorlar</h2>
    <p class="section-desc reveal">Har bir yo'nalish bo'yicha maxsus o'qitilgan AI mentorlar 24/7 rejimida savollaringizni javob berish va bilim berishga tayyor.</p>
    <div class="mentors-grid">
      <?php foreach($mentors as $mentor): ?>
      <div class="mentor-card reveal">
        <div class="mentor-card-banner">
          <div class="mentor-avatar-wrap">
            <img src="<?=Url::base()?>/images/mentors/<?=$mentor['landing_img']?>">
          </div>
        </div>
        <div class="mentor-card-body">
          <div class="mentor-card-top"><span class="mentor-badge"><span class="mentor-ai-dot"></span>AI Mentor</span></div>
          <div class="mentor-name"><?=$mentor['name']?></div>
          <div class="mentor-role"><?=$mentor['title']?></div>
          <div class="mentor-desc"><?=$mentor['description']?></div>
          <?php
            $skills = json_decode($mentor['skills'], true);
          ?>
          <div class="mentor-skills">
            <?php foreach($skills as $skill): ?>
            <span class="skill-pill"><?=$skill['name']?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="divider"></div>

<section class="courses-section" id="courses">
  <div class="section-inner">
    <div class="section-tag">Ta'lim dasturi</div>
    <h2 class="section-title reveal">Kurslar</h2>
    <p class="section-desc reveal">Har bir kurs AI tomonidan o'tiladi. Boshlang'ichdan professionalga qadar barcha darajalar uchun individual o'quv yo'li taqdim etiladi</p>
    <div class="courses-grid">
      <?php foreach($courses as $course): ?>
      <div class="course-card reveal">
        <div class="course-thumb">
          <img src="<?=Url::base()?>/images/courses/<?=$course['img']?>">
        </div>
        <div class="course-body">
          <div class="course-title"><?=$course['name']?></div>
          <div class="course-mentor">Mentor: <?=$course['mentor_name']?> (AI)</div>
          <div class="course-desc"><?=$course['title']?></div>
          <div class="course-footer">
            <div class="course-meta">
              <div class="course-meta-item"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?=$course['modules_count']?> ta modul</div>
              <div class="course-meta-item"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg><?=$course['topics_count']?> ta dars</div>
            </div>
            <a href="<?=Url::to(['site/course-preview', 'course_id' => $course['id']])?>" class="btn-course">Kursga o'tish →</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="courses-cta reveal">
      <a href="<?=Url::to(['site/courses'])?>" class="btn-outline-lg">
        Barcha kurslarni ko'rish
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>
</section>