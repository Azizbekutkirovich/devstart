<?php

use yii\helpers\Url;
use yii\web\View;

$this->title = "Сhat";
$this->registerMetaTag([
    'name' => 'topic-flow',
    'content' => $topic_type
]);
$this->registerCssFile("https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css");
$this->registerCssFile(Url::base()."/css/chat.css");
?>
<div class="chat-container" id="chat">
	<div class="message bot">
		<h1><?=$topic_name?></h1>
	</div>
</div>
<div class="chat-input" id="chatInput" style="display:none;">
	<textarea id="userInput" class="auto-grow" placeholder="Mavzu bo'yicha savolingizni yozing..."></textarea>
	<button id="askQuestionBtn">Yuborish</button>
</div>
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
?>