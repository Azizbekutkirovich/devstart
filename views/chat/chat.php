<?php

use yii\helpers\Url;
use yii\web\View;

/*
  view data standart

  topic_type

  current_stage

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

  <div id="chat"></div>

  <div class="chat-input-wrap" id="chatInput">
    <textarea id="userInput" class="auto-grow" placeholder="Mavzu bo'yicha savolingizni yozing..."></textarea>
    <button id="askQuestionBtn">Yuborish ↑</button>
  </div>
</main>
<script type="text/javascript">
  let botAvatar = "<?=Url::base()?>/images/mentors/<?=$mentor_avatar?>";
  let text = '';
</script>
<script type="text/javascript">
  window.__USER_ROLE__ = "<?= (Yii::$app->user->isGuest) ? "guest" : "user"; ?>";
  window.__CHAT_HISTORY__ = [
    <?php if (isset($messages) && !empty($messages)): ?>
      <?php foreach ($messages as $message): ?>
        {
        // restore topic
        <?php if ($message['sender_role'] == 'mentor' && $message['type'] == 'topic'): ?>
          <?php
            $contentText = json_decode($message['content'], true)['text'] ?? '';
            $sfContent = json_encode($contentText, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
          ?>
          "type": "bot_topic",
          "content": <?= $sfContent ?>

        // restore system commands
        <?php elseif ($message['sender_role'] == 'system' && $message['type'] == 'command'): ?>
          <?php
            $contentText = json_decode($message['content'], true)['text'] ?? '';
            $sfContent = json_encode($contentText, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
          ?>
          "type": "user_message",
          "content": <?= $sfContent ?>

        // restore user message
        <?php elseif ($message['sender_role'] == 'user' && $message['type'] == 'text'): ?>
          <?php
            $contentText = json_decode($message['content'], true)['text'] ?? '';
            $sfContent = json_encode($contentText, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
          ?>
          "type": "user_message",
          "content": <?= $sfContent ?>

        // restore quiz
        <?php elseif ($message['sender_role'] == 'mentor' && $message['type'] == 'quiz'): ?>
          <?php
            $quiz_content = json_decode($message['content'], true);
            $content_to_fr = [];
            foreach ($quiz_content as $content) {
              $content_to_fr["questions"][] = $content['data'];
              if (isset($content['user-results'])) $content_to_fr["results"][] = array_merge($content['user-results'], $content['answers']);
              else $content["results"] = [];
              $sfContent = json_encode($content_to_fr, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);   
            }
          ?>
          "type": "quiz",
          "data": <?= $sfContent ?>

        // restore practice
        <?php elseif ($message['sender_role'] == 'mentor' && $message['type'] == 'practice'): ?>
          <?php
            $contentText = json_decode($message['content'], true)['text'] ?? '';
            $sfContent = json_encode($contentText, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
          ?>
          "type": "bot_practice",
          "content": <?= $sfContent ?>

        // restore mentor message
        <?php elseif ($message['sender_role'] == 'mentor' && $message['type'] == 'text'): ?>
          <?php
            $contentText = json_decode($message['content'], true)['text'] ?? '';
            $sfContent = json_encode($contentText, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
          ?>
          "type": "bot_message",
          "content": <?= $sfContent ?>          

        // restore practice result
        <?php elseif ($message['sender_role'] == 'mentor' && $message['type'] == 'practice_result'): ?>
          <?php
            $contentText = json_decode($message['content'], true)['text'] ?? '';
            $sfContent = json_encode($contentText, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
          ?>
          "type": "bot_practice_result",
          "content": <?= $sfContent ?>          
        <?php endif; ?>
        },
      <?php endforeach; ?>
    <?php endif; ?>
  ];
  <?php if (!Yii::$app->user->isGuest): ?>
  window.__RESUME__ = {
    "topic_completed": "<?= $topic_completed ?>",
    <?php if (!empty($full_content)): ?>
    "lesson_content": <?= $full_content ?>,
    <?php endif; ?>
    <?php if (isset($messages) && !empty($messages)): ?>
      <?php foreach ($messages as $message):  ?>
        <?php if ($message['sender_role'] == 'mentor' && $message['type'] == 'practice'): ?>
          <?php
            $contentText = json_decode($message['content'], true)['text'] ?? '';
            $sfContent = json_encode($contentText, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
          ?>
          "practices": <?= $sfContent ?>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  }
  <?php endif; ?>
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
  $this->registerJsFile(Url::base()."/js/chat/restorer.js",
  [
      'depends' => [\yii\web\JqueryAsset::class],
      'position' => View::POS_END,
  ]);
  $this->registerJsFile(Url::base()."/js/chat/app.js",
  [
      'depends' => [\yii\web\JqueryAsset::class],
      'position' => View::POS_END,
  ]);
?>