<?php
use yii\helpers\Url;

$this->title = 'Bosh sahifa';
$this->registerCssFile(Url::base()."/css/home.css");
?>
<section class="home">
  <div class="home-inner">

    <div class="home-header">
      <h2 class="home-title">
        Xush kelibsiz
        <span class="username">
          <?= Yii::$app->user->isGuest ? "👤 Mehmon" : Yii::$app->user->fullname ?>
        </span>
      </h2>

      <div class="home-meta">
        <span class="badge badge-category">
          💻 <?= $category ?>
        </span>

        <span class="badge badge-language">
          🧩 <?= $language ?>
        </span>
      </div>
    </div>

    <h4 class="section-title">📚 Bo'limlar</h4>

    <div class="home-menu">
      <?php foreach($topics_data as $index => $section): ?>
        <button class="home-tab <?= $index === 0 ? 'active' : '' ?>" data-section="section-<?= $section['id'] ?>"
        >
          <?= $section['name'] ?>
        </button>
      <?php endforeach; ?>
    </div>

    <h4 class="section-title">📖 Mavzular</h4>

    <div class="topics">
      <?php $i = 0; ?>

      <?php foreach($topics_data as $index => $section): ?>
      <div class="topics-section section-<?= $index + 1 ?> <?= $index === 0 ? 'active' : '' ?>"  id="section-<?= $section['id'] ?>">

        <?php foreach($section['topics'] as $topic): ?>
          <div class="topic">

            <div class="topic-header">
              <span class="topic-number"><?= $i + 1 ?>.</span>
              <h3 class="topic-title"><?= $topic['title'] ?></h3>
            </div>

            <div class="progress-item">
              <p class="progress-text">
                Mavzuni o'rganilganligi — 0%
              </p>

              <div class="progress-bar">
                <div class="progress-fill w-0"></div>
              </div>
            </div>

            <a class="into-topic" href="<?= Url::to(array_merge(
                ['chat/chat-preview', 'topic_id' => $topic['id']],
                Yii::$app->user->isGuest ? ['level_id' => $level_id] : []
              )) ?>"
            >
              ➡️ Mavzuga o'tish
            </a>
          </div><br>

          <?php $i++; ?>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="section-nav">
      <button class="section-btn prev" disabled>
        ⬅ Oldingi bo‘lim
      </button>

      <button class="section-btn next">
        Keyingi bo‘lim ➡
      </button>
    </div>
  </div>
</section>
<script>
  const tabs = document.querySelectorAll('.home-tab');
  const sections = document.querySelectorAll('.topics-section');
  const prevBtn = document.querySelector('.section-btn.prev');
  const nextBtn = document.querySelector('.section-btn.next');

  let currentIndex = 0;

  function updateSection(index) {
    tabs.forEach(t => t.classList.remove('active'));
    sections.forEach(s => s.classList.remove('active'));

    tabs[index].classList.add('active');
    sections[index].classList.add('active');

    prevBtn.disabled = index === 0;
    nextBtn.disabled = index === sections.length - 1;

    currentIndex = index;

    const sectionId = sections[index].id;
    history.replaceState(null, null, '#' + sectionId);
  }

  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => {
      updateSection(index);
    });
  });

  prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) updateSection(currentIndex - 1);
  });

  nextBtn.addEventListener('click', () => {
    if (currentIndex < sections.length - 1) updateSection(currentIndex + 1);
  });

  function initFromHash() {
    const hash = window.location.hash;
    if (!hash) {
      updateSection(0);
      return;
    }

    const index = Array.from(sections).findIndex(s => '#' + s.id === hash);
    updateSection(index >= 0 ? index : 0);
  }

  initFromHash();
</script>