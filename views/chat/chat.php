<?php

use yii\helpers\Url;
use yii\web\View;

/*
  view data standart

  topic_type

  topic_name

  mentor_avatar

*/

$this->title = "Сhat";
$this->registerMetaTag([
    'name' => 'topic-flow',
    'content' => $topic_type
]);
$this->registerCssFile("https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css");
$this->registerCssFile(Url::base()."/css/dashboard/chat.css");
?>
<!-- MAIN -->
<main class="main">
  <!-- CHAT TOPBAR -->
  <div class="chat-topbar">
    <div class="topic-chip">
      <span class="topic-chip-badge">📌 MAVZU</span>
      <span class="topic-chip-name"><?=$topic_name?></span>
    </div>
  </div>

  <!-- CHAT MESSAGES -->
  <div id="chat"></div>

  <!-- CHAT INPUT -->
  <div class="chat-input-wrap" id="chatInput">
    <textarea id="userInput" class="auto-grow" placeholder="Mavzu bo'yicha savolingizni yozing..."></textarea>
    <button id="askQuestionBtn">Yuborish ↑</button>
  </div>
</main>
<script type="text/javascript">
  let botAvatar = "<?=Url::base()?>/images/mentors/<?=$mentor_avatar?>";
  let text = '';
</script>
<?php
  $this->registerJsFile("https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js");
  $this->registerJsFile("https://cdn.jsdelivr.net/npm/marked/marked.min.js");
  $this->registerJsFile(Url::base()."/js/chat/helpers.js",
  [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => View::POS_END,
  ]);
  $this->registerJsFile(Url::base()."/js/chat/flowManager.js",
  [
      'depends' => [\yii\web\JqueryAsset::class],
      'position' => View::POS_END,
  ]);
  $this->registerJsFile(Url::base()."/js/chat/topic.js",
  [
      'depends' => [\yii\web\JqueryAsset::class],
      'position' => View::POS_END,
  ]);
  $this->registerJsFile(Url::base()."/js/chat/quiz.js",
  [
      'depends' => [\yii\web\JqueryAsset::class],
      'position' => View::POS_END,
  ]);
  $this->registerJsFile(Url::base()."/js/chat/practice.js",
  [
      'depends' => [\yii\web\JqueryAsset::class],
      'position' => View::POS_END,
  ]);
  $this->registerJsFile(Url::base()."/js/chat/chat.js",
  [
      'depends' => [\yii\web\JqueryAsset::class],
      'position' => View::POS_END,
  ]);
  $this->registerJsFile(Url::base()."/js/chat/app.js",
  [
      'depends' => [\yii\web\JqueryAsset::class],
      'position' => View::POS_END,
  ]);
  if (isset($messages)) {
    foreach ($messages as $message) {
      switch ($message['type']) {
        case 'text':
          $contentText = json_decode($message["content"], true)['text'] ?? '';

          // 2. Matnni JS uchun xavfsiz JSON string ko'rinishiga o'tkazamiz
          // json_encode o'zi avtomatik ravishda " " qo'shtirnoqlarini qo'shadi
          $jsSafeContent = json_encode($contentText, JSON_UNESCAPED_UNICODE);

          if ($message['sender_role'] == 'user' || $message['sender_role'] == 'system') {
            $this->registerJs(<<<JS
              text = $jsSafeContent;
              restoreText(text, addUserMessage);
            JS
            );
          } else {
            $this->registerJs(<<<JS
              text = $jsSafeContent;
              restoreText(text, addBotMessage);
            JS
            );
          }
          break;
        
        default:
          // code...
          break;
      }
    }
  }
?>