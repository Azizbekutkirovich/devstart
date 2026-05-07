<?php
use yii\helpers\Url;

$this->title = 'Bosh sahifa';
$this->registerCssFile(Url::base()."/css/dashboard/home.css");
/*
  view-data-standart:

  fullname

  level = [
    "title"
    "img"
  ]
  
  course = [
    "name"
    "progress"
  ]
  
  mentor = [
    "name"
    "img"
  ]
  
  modules = [
    [
      'name'
      'topics_count'
      'progress'
      'topics' => [
          'id'
          'title'
          'progress'
      ] 
    ],
    ...
  ]
*/
?>
<!-- MAIN CONTENT -->
<main class="main">

  <!-- TOP -->
  <div class="topbar">
    <div class="welcome-text">
      <h1>Salom, <?=$fullname?>! 👋</h1>
      <p>O'rganishda davom eting. Siz yaxshi yo'ldasiz.</p>
    </div>
    <div class="streak-badge">
      <img style="width: 20px; height: 20px;" src="<?=Url::base()?>/images/icons/<?=$level['img']?>" alt="daraja rasmi">
      <div>
        <div class="streak-num"><?=$level['title']?></div>
        <div style="font-size:.80rem; color:var(--muted);">kurs o'tilish darajasi</div>
      </div>
    </div>
  </div>

  <!-- COURSE CARD -->
  <div class="section-label">Siz tanlagan kurs</div>
  <div class="course-hero">
    <div class="course-hero-bg"></div>
    <div class="course-hero-grid"></div>
    <div class="course-hero-overlay"></div>

    <div class="course-hero-content">
      <div>
        <div class="course-tag">⚡ Faol kurs</div>
      </div>
      <div class="course-title-block">
        <h2><?=$course['name']?></h2>
        <div class="course-mentor">
          <div class="mentor-avatar">
            <img src="<?=Url::base()?>/images/mentors/<?=$mentor['img']?>" alt='mentor-rasmi'>
          </div>
          <div class="mentor-info">
            <div class="mentor-label">AI Mentor</div>
            <div class="mentor-name"><?=$mentor['name']?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="course-stats">
      <div class="overall-progress-label">Umumiy progress</div>
      <div class="circular-progress">
        <svg viewBox="0 0 54 54" width="64" height="64">
          <circle class="cp-bg" cx="27" cy="27" r="22"/>
          <circle class="cp-fill" cx="27" cy="27" r="22"/>
        </svg>
        <div class="cp-text"><?=$course['progress']?>%</div>
      </div>
    </div>
  </div>

  <!-- MODULES -->
  <div class="section-label">Kurs modullari</div>
  <div class="modules-area">

    <!-- MODULE 1 -->
    <?php $i = 1; ?>
    <?php foreach($modules as $module): ?>
    <div class="module-card <?php echo ($i == 1) ? "open" : ""; ?>" id="mod<?=$i?>">
      <div class="module-header" onclick="toggleModule('mod<?=$i?>')">
        <div class="module-num">M<?=$i?></div>
        <div class="module-meta">
          <div class="module-name"><?=$module['name']?></div>
          <div class="module-subtitle"><?=$module['topics_count']?> ta mavzu · <?=$module['progress']?>% yakunlangan</div>
        </div>
        <div class="module-progress-wrap">
          <div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$module['progress']?>%"></div></div>
          <div class="module-pct"><?=$module['progress']?>%</div>
        </div>
        <div class="toggle-icon">▾</div>
      </div>
      <div class="module-body">
        <div class="module-progress-bar">
          <div class="mpb-label">Modul progressi</div>
          <div class="mpb-track"><div class="mpb-fill" style="width:<?=$module['progress']?>%"></div></div>
          <div class="mpb-pct"><?=$module['progress']?>%</div>
        </div>
        <?php foreach($module['topics'] as $topic): ?>
        <div class="topic-item">
          <div class="topic-status status-<?php echo ($topic['progress'] === 100) ? "done" : (($topic['progress'] > 0) ? "progress" : "") ?>"><?php echo ($topic['progress'] === 100) ? "✓" : (($topic['progress'] > 0) ? "◕" : "▶") ?></div>
          <div class="topic-info">
            <div class="topic-name"><?=$topic['title']?></div>
            <div class="topic-bar-row">
              <div class="topic-bar-track"><div class="topic-bar-fill fill-<?=$topic['progress']?>"></div></div>
              <div class="topic-pct"><?=$topic['progress']?>%</div>
            </div>
          </div>
          <?php
            $chat_page_url = (!Yii::$app->user->isGuest) ? Url::to(['chat/chat', 'topic_id' => $topic['id']]) : Url::to(['chat/chat-preview', 'course_id' => Yii::$app->request->get("course_id"), 'topic_id' => $topic['id'], 'level_id' => Yii::$app->request->get("level_id")])
          ?>
          <button class="topic-btn active" onclick="window.location.href='<?=$chat_page_url?>'">Darsga o'tish →</button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php $i++ ?>
    <?php endforeach; ?>
  </div>
</main>
<?php
  $this->registerJsFile(Url::base()."/js/dashboard/home.js");
?>
<script type="text/javascript">
  let course_progress_percent = <?=$course['progress']?>;
</script>