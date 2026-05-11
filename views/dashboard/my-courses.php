<?php
use yii\helpers\Url;

$this->title = 'Bosh sahifa';
$this->registerCssFile(Url::base()."/css/dashboard/my-courses.css");
/*
  view-data-standart:
  
  active_course = [
    "name"
    "progress"
  ]

  active_level = [
    "title"
    "img"
  ]

  active_mentor = [
    "name"
    "img"
  ]

  other_courses = [
    [
      "user_data_id"
      "name"
      "progress"
      "course_level" => [
        "title"
        "img"
      ]
      "mentor" => [
        "name"
        "img"
      ]
    ],
    ....
  ]
*/
?>
<main class="main">
  <div class="active-course">  
    <div class="section-label">Siz o'qiyotgan kurs</div>
    <div class="course-hero">
      <div class="course-hero-bg"></div>
      <div class="course-hero-grid"></div>
      <div class="course-hero-overlay"></div>

      <div class="course-hero-content">
        <div class="course-tags-wrapper">
          <div class="course-tag active-tag">⚡ Faol kurs</div>
          <div class="course-tag level-tag">
             <img src="<?=Url::base()?>/images/icons/<?=$active_level['img']?>" alt="level">
             <?=$active_level['title']?>
          </div>
        </div>
        <div class="course-title-block">
          <h2><?=$active_course['name']?></h2>
          <div class="course-mentor">
            <div class="mentor-avatar">
              <img src="<?=Url::base()?>/images/mentors/<?=$active_mentor['img']?>" alt='mentor-rasmi'>
            </div>
            <div class="mentor-info">
              <div class="mentor-label">AI Mentor</div>
              <div class="mentor-name"><?=$active_mentor['name']?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="course-stats">
        <div class="overall-progress-label">Umumiy progress</div>
        <div class="circular-progress" data-percent="<?=$active_course['progress']?>">
          <svg viewBox="0 0 54 54" width="64" height="64">
            <circle class="cp-bg" cx="27" cy="27" r="22"/>
            <circle class="cp-fill" cx="27" cy="27" r="22"/>
          </svg>
          <div class="cp-text"><?=$active_course['progress']?>%</div>
        </div>
      </div>
    </div>
  </div>
  <div class="other-courses">
    <div class="section-label">Siz tanlagan boshqa kurslar</div>
    <?php if (!empty($other_courses)): ?>
    <?php foreach ($other_courses as $course): ?>
    <div class="course-hero" onclick="changeCourse(<?=$course['user_data_id']?>)">
      <div class="course-hero-bg"></div>
      <div class="course-hero-grid"></div>
      <div class="course-hero-overlay"></div>

      <div class="course-hero-content">
        <div class="course-tags-wrapper">
          <div class="course-tag level-tag">
             <img src="<?=Url::base()?>/images/icons/<?=$course['course_level']['img']?>" alt="level">
             <?=$course['course_level']['title']?>
          </div>
        </div>
        <div class="course-title-block">
          <h2><?=$course['name']?></h2>
          <div class="course-mentor">
            <div class="mentor-avatar">
              <img src="<?=Url::base()?>/images/mentors/<?=$course['mentor']['img']?>" alt='mentor-rasmi'>
            </div>
            <div class="mentor-info">
              <div class="mentor-label">AI Mentor</div>
              <div class="mentor-name"><?=$course['mentor']['name']?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="course-stats">
        <div class="overall-progress-label">Umumiy progress</div>
        <div class="circular-progress" data-percent="<?=$course['progress']?>">
          <svg viewBox="0 0 54 54" width="64" height="64">
            <circle class="cp-bg" cx="27" cy="27" r="22"/>
            <circle class="cp-fill" cx="27" cy="27" r="22"/>
          </svg>
          <div class="cp-text"><?=$course['progress']?>%</div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
      <div class="section-label" style="font-size: 1.2rem; color: red;">MAVJUD EMAS!</div>
    <?php endif; ?>
  </div><br><br>
  <a href="<?=Url::to(['site/courses'])?>" class="btn btn-primary" style='text-decoration: none;'>➕ Yangi kursni boshlash</a>
</main>
<script type="text/javascript">
  let change_course_url = '<?=Url::to(['auth/change-course'])?>';
</script>
<?php
  $this->registerJsFile(Url::base()."/js/dashboard/my-courses.js");
?>