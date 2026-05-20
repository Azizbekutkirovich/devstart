<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\UserData;
use app\models\Courses;
use app\models\Topics;
use app\models\Levels;
use app\models\Chats;
use app\models\MasterLessons;
use app\models\Messages;
use app\services\GeminiApiService;
use app\services\PromptService;

class ChatController extends Controller
{
    public $layout = "dashboard";

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['chat', 'chat-preview'],
                'rules' => [
                    ['actions' => ['chat'],         'allow' => true, 'roles' => ['@']],
                    ['actions' => ['chat-preview'],  'allow' => true, 'roles' => ['?']],
                ],
            ],
        ];
    }

    public function actionGenerateTopic(): void
    {
        $this->requireAjax();

        $body  = Yii::$app->request->getBodyParams();
        $course = $this->resolveCourse();
        $topic = $this->resolveTopic((int) ($body['topic_id'] ?? 0), $course['id']);
        $level = $this->resolveLevel();

        $mentor = $course['mentor'];

        $prompt = $this->resolvePrompt('generate-topic', [
            'mentor_name' => $mentor['name'],
            'mentor_personality' => $mentor['personality'],
            'course_name' => $course['name'],
            'level_title' => $level['title'],
            'level_description' => $level['description'],
            'topic_name' => $topic['title'],
            'key_concepts' => $topic['key_concepts']
        ], $topic['type']);

        if (Yii::$app->user->isGuest) {
            $this->handleGuestGenerateTopic($prompt);
        } else {
            $this->handleUserGenerateTopic($prompt, $topic);
        }
    }

    public function actionGeneratePractice(): void
    {
        $this->requireAjax();

        $body           = Yii::$app->request->getBodyParams();
        $course = $this->resolveCourse();
        $topic = $this->resolveTopic((int) ($body['topic_id'] ?? 0), $course['id']);
        $level = $this->resolveLevel();
        $lesson_content = $this->requireField($body, 'lesson_content');

        $prompt = $this->resolvePrompt('generate-practice', [
            'lesson_content' => $lesson_content,
            'course_name'       => $course['name'],
            'level_title'          => $level['title'],
            'level_description'          => $level['description'],
            'topic_name'     => $topic['title'],
        ]);

        if (Yii::$app->user->isGuest) {
            $this->handleGuestGeneratePractice($prompt);
        } else {
            $this->handleUserGeneratePractice($prompt, $topic);
        }
    }

    public function actionAskQuestionAboutTopic(): void
    {
        $this->requireAjax();

        $body          = Yii::$app->request->getBodyParams();
        $course = $this->resolveCourse();
        $topic = $this->resolveTopic((int) ($body['topic_id'] ?? 0), $course['id']);
        $level = $this->resolveLevel();
        $user_question = $this->requireField($body, 'user_question');

        $prompt = $this->resolvePrompt('ask-question-about-topic', [
            'course_name'      => $course['name'],
            'level_title'         => $level['title'],
            'level_description'         => $level['description'],
            'topic_name'    => $topic['title'],
            'user_question' => $user_question,
        ]);

        if (Yii::$app->user->isGuest) {
            $this->handleGuestAskQuestion($prompt);
        } else {
            $this->handleUserAskQuestion($prompt, $topic, $user_question);
        }
    }

    public function actionCheckPractice(): void
    {
        $this->requireAjax();

        $body         = Yii::$app->request->getBodyParams();
        $course = $this->resolveCourse();
        $topic = $this->resolveTopic((int) ($body['topic_id'] ?? 0), $course['id']);
        $level = $this->resolveLevel();
        $practices    = $this->requireField($body, 'practices');
        $user_answers = $body['answers'] ?? [];

        if (empty($user_answers)) {
            $this->sendStreamError("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang");
        }

        $prompt = $this->resolvePrompt('check-practice', [
            'course_name'     => $course['name'],
            'level_title'        => $level['title'],
            'level_description'        => $level['description'],
            'practices'    => $practices,
            'user_answers' => $this->formatAnswers($user_answers),
        ]);

        if (Yii::$app->user->isGuest) {
            $this->handleGuestCheckPractice($prompt);
        } else {
            $this->handleUserCheckPractice($prompt, $topic, $user_answers);
        }
    }

    public function actionGenerateQuizTest()
    {
        $this->requireAjax();

        $body           = Yii::$app->request->getBodyParams();
        $course = $this->resolveCourse();
        $topic = $this->resolveTopic((int) ($body['topic_id'] ?? 0), $course['id'], useJson: true);
        $level = $this->resolveLevel(useJson: true);
        $lesson_content = $this->requireField($body, 'lesson_content', useJson: true);


        $prompt = $this->resolvePrompt('generate-quiz-test', [
            'lesson_content' => $lesson_content,
            'course_name'       => $course['name'],
            'level_title'          => $level['title'],
            'level_description'          => $level['description'],
            'topic_name'     => $topic['title'],
        ]);

        if (Yii::$app->user->isGuest) {
            return $this->handleGuestGenerateQuizTest($prompt);
        } else {
            return $this->handleUserGenerateQuizTest($prompt, $topic);
        }
    }

    public function actionCheckQuiz()
    {
        $this->requireAjax();

        $body     = Yii::$app->request->getBodyParams();
        $selected = $body['selected'] ?? '';

        if ($selected === '') {
            return $this->asJson([
                'success' => false,
                'message' => "Siz tanlagan javoblar kelmadi! Iltimos sahifani qayta yuklang",
            ]);
        }

        if (Yii::$app->user->isGuest) {
            return $this->handleGuestCheckQuiz($selected);
        } else {
            return $this->handleUserCheckQuiz($selected);
        }
    }

    public function actionChat(int $topic_id) 
    {
        $course = Courses::findOne(Yii::$app->user->identity->activeData['course_id']);
        $topic = Topics::find()
            ->alias('t')
            ->select(['t.id', 't.module_id', 't.type', 't.title'])
            ->innerJoin('course_modules cm', 'cm.module_id = t.module_id')
            ->where([
                't.id' => $topic_id,
                'cm.course_id' => Yii::$app->user->identity->activeData['course_id']
            ])
            ->with(['module'])
            ->asArray()
            ->one();
        $chat_data = $this->resolveChat($topic_id, ['current_stage']);
        $messages = Messages::find()
            ->where(['chat_id' => $chat_data['id']])
            ->orderBy(['created_at' => SORT_ASC])
            ->asArray()
            ->all();

        $topic_completed = false;
        $full_content = "";
        if (!empty($messages)) {
            $msl_data = MasterLessons::find()->select(['total_parts', 'full_content'])->where(['chat_id' => $chat_data['id']])->asArray()->one();
            if (!empty($msl_data)) {
                if ($chat_data['current_stage'] >= $msl_data['total_parts']) {
                    $topic_completed = true;
                    $full_content = $msl_data['full_content'];
                }
            }
        }

        if (!$topic) {
            return $this->goBack();
        }

        return $this->render('chat', [
            'topic_type' => $topic['type'],
            'topic_name' => $topic['title'],
            "current_stage" => $chat_data['current_stage'] ?? 0,
            'mentor_avatar' => $course->mentor->chat_img,
            'messages' => $messages,
            'topic_completed' => $topic_completed,
            'full_content' => $full_content
        ]);        
    }

    public function actionChatPreview(int $course_id, int $topic_id, int $level_id)
    {
        $levelExists = Levels::find()->where(['id' => $level_id])->exists();

        if (!$levelExists) {
            return $this->goBack();
        }

        $course = Courses::findOne($course_id);

        if (!$course) {
            return $this->goBack();
        }

        $topic = Topics::find()
            ->alias('t')
            ->select(['t.id', 't.module_id', 't.type', 't.title'])
            ->innerJoin('course_modules cm', 'cm.module_id = t.module_id')
            ->where([
                't.id' => $topic_id,
                'cm.course_id' => $course_id
            ])
            ->with(['module'])
            ->asArray()
            ->one();

        if (!$topic) {
            return $this->goBack();
        }

        return $this->render('chat', [
            'topic_type' => $topic['type'],
            'topic_name' => $topic['title'],
            'mentor_avatar' => $course->mentor->chat_img
        ]);
    }

    private function handleGuestGenerateTopic(string $prompt): void
    {
        $this->streamResponse($prompt);
        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    private function handleGuestGeneratePractice(string $prompt): void
    {
        $this->streamResponse($prompt);
        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    private function handleGuestAskQuestion(string $prompt): void
    {
        $this->streamResponse($prompt);
        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    private function handleGuestCheckPractice(string $prompt): void
    {
        $this->streamResponse($prompt);
        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    private function handleGuestGenerateQuizTest(string $prompt)
    {
        $raw  = (new GeminiApiService())->getContent($prompt);

        if ($raw === 'network error') {
            return $this->asJson(['success' => false, 'message' => 'Tizimda xatolik yuz berdi!']);
        }

        $json = $this->parseJsonResponse($raw);
        if ($json === null) {
            return $this->asJson(['success' => false, 'message' => "Javobni o'qishda xatolik yuz berdi!"]);
        }

        [$quiz_data, $quiz_clean] = $this->splitQuizData($json['quiz']);

        Yii::$app->session->setFlash('quiz_test_data', $quiz_data);

        return $this->asJson(['success' => true, 'data' => $quiz_clean]);
    }

    private function handleGuestCheckQuiz(array $selected)
    {
        $quiz_data = Yii::$app->session->getFlash('quiz_test_data');

        if (empty($quiz_data)) {
            return $this->asJson([
                'success' => false,
                'message' => "Testlarni tekshirishda xatolik! Iltimos sahifani qayta yuklang",
            ]);
        }

        return $this->asJson([
            'success' => true,
            'data'    => ['results' => $this->buildQuizResults($selected, $quiz_data)],
        ]);
    }

    private function handleUserGenerateTopic(string $prompt, array $topic): void
    {
        $fullContent = $this->streamFirstPartOnly($prompt);

        if ($fullContent === null) {
            return;
        }

        $parts      = explode('[NEXT]', $fullContent);
        $totalParts = count($parts);

        $chatId = $this->resolveChat($topic['id'])['id'];
        if (!$chatId) exit();

        $topicType   = $topic['type'] ?? 'theory';
        $totalStages = $this->calculateTotalStages($topicType, $totalParts);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $updatedRows = Chats::updateAll(
                [
                    'total_stages'  => $totalStages,
                    'current_stage' => new \yii\db\Expression('current_stage + 1'),
                ],
                ['id' => $chatId]
            );

            $masterLesson               = new MasterLessons();
            $masterLesson->chat_id      = $chatId;
            $masterLesson->full_content = $fullContent;
            $masterLesson->total_parts  = $totalParts;

            if (!$masterLesson->save()) {
                throw new \Exception("MasterLesson_SAVE_ERROR");
            }

            $firstPartText = trim($parts[0]);

            $systemMsg = Messages::create($chatId, 'system', 'text', ['text' => 'Darsni boshlash']);
            $mentorMsg = Messages::create($chatId, 'mentor', 'topic', ['text' => $firstPartText], 0);

            if (!$systemMsg || !$mentorMsg) {
                throw new \Exception("Messages_SAVE_ERROR");
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            
            Yii::error("Xatolik: handleUserGenerateTopicda: chat_id={$chatId}, xatolik xabari: {$e->getMessage()}");

            $this->sendStreamError("Xatolik yuz berdi! Iltimos sahifani qayta yuklang");
        }

        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    public function actionContinueTopic(): void
    {
        $this->requireAjax();
        $body  = Yii::$app->request->getBodyParams();
        $chatId = $this->resolveChat((int) ($body['topic_id'] ?? 0))['id'];   
        $lastMentorMessage = Messages::find()
            ->select(['id', 'step_topic_index'])
            ->where([
                'chat_id'     => $chatId,
                'sender_role' => 'mentor',
                'type'        => 'topic',
            ])
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->one();

        if (!$lastMentorMessage) {
            $this->sendStreamError('Xatolik yuz berdi! Iltimos sahifani qayta yuklang');
            return;
        }

        $currentIndex = (int) $lastMentorMessage['step_topic_index'];
        $nextIndex    = $currentIndex + 1;

        $masterLesson = MasterLessons::find()
            ->where(['chat_id' => $chatId])
            ->one();

        if (!$masterLesson) {
            Yii::error("continueTopic: masterLesson topilmadi: chat_id={$chatId}");
            $this->sendStreamError('Xatolik yuz berdi! Iltimos sahifani qayta yuklang');
            return;
        }

        $parts      = explode('[NEXT]', $masterLesson->full_content);
        $totalParts = (int) $masterLesson->total_parts;

        if (!isset($parts[$nextIndex])) {
            Yii::error("continueTopic: masterLessonda mavzu qismi topilmadi: chat_id={$chatId}, step={$nextIndex}");
            $this->sendStreamError('Keyingi qism mavjud emas! Iltimos sahifani qayta yuklang');
            return;
        }

        $nextPartText = trim($parts[$nextIndex]);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $systemMsg = Messages::create($chatId, 'system', 'command', ['text' => 'Davom etish']);
            if (!$systemMsg) {
                throw new \Exception("System xabari saqlanmadi: chat_id={$chatId}");
            }

            $mentorMsg = Messages::create($chatId, 'mentor', 'topic', ['text' => $nextPartText], $nextIndex);
            if (!$mentorMsg) {
                throw new \Exception("Mentor xabari saqlanmadi: chat_id={$chatId}, step={$nextIndex}");
            }

            $updateCount = Chats::updateAll(
                ['current_stage' => new \yii\db\Expression('current_stage + 1')],
                ['id' => $chatId]
            );

            if ($updateCount === 0) {
                throw new \Exception("Chat holati yangilanmadi: chat_id={$chatId}");
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();

            Yii::error("continueTopic xatoligi: " . $e->getMessage());

            $this->sendStreamError("Noma'lum xatolik yuz berdi! Iltimos sahifani qayta yuklang");
            return;
        }

        echo 'data: ' . json_encode(['content' => $nextPartText]) . "\n\n";
        flush();
        if (($nextIndex + 1) === $totalParts) {
            echo "data: [DONE:end]\n\n";
        } else {
            echo "data: [DONE:more]\n\n";
        }
        flush();

        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    private function streamFirstPartOnly(string $prompt): ?string
    {
        $gemini_api = new GeminiApiService();
        $gemini_api->prepareSystemForStreaming();

        $fullContent   = '';
        $firstPartSent = false;

        $tailBuffer = '';

        try {
            $gemini_api->streamContent($prompt, function (string $chunk) use (
                &$fullContent,
                &$firstPartSent,
                &$tailBuffer
            ) {
                $fullContent .= $chunk;

                if ($firstPartSent) {
                    return;
                }

                $search  = $tailBuffer . $chunk;
                $nextPos = strpos($search, '[NEXT]');

                if ($nextPos !== false) {
                    $toSend = substr($search, 0, $nextPos);

                    if ($toSend !== '') {
                        echo 'data: ' . json_encode(['content' => $toSend]) . "\n\n";
                        echo "data: [DONE:more]\n\n";
                        flush();
                    }

                    $firstPartSent = true;
                    $tailBuffer    = '';

                } else {
                    $toSend     = strlen($search) > 6 ? substr($search, 0, -6) : '';
                    $tailBuffer = strlen($search) > 6 ? substr($search, -6) : $search;

                    if ($toSend !== '') {
                        echo 'data: ' . json_encode(['content' => $toSend]) . "\n\n";
                        flush();
                    }
                }
            });

            if (!$firstPartSent && $tailBuffer !== '') {
                echo 'data: ' . json_encode(['content' => $tailBuffer]) . "\n\n";
                flush();
            }

            echo "data: [DONE:end]\n\n";
            flush();

            return $fullContent;

        } catch (\Throwable $e) {
            $this->sendStreamError($this->resolveStreamErrorMessage($e));
            return null;
        }
    }


    private function calculateTotalStages(string $topicType, int $total_parts): int
    {
        switch ($topicType) {
            case 'theory':
                return $total_parts;

            case 'practice':
                return 2;

            case 'lesson':
                return $total_parts + 2 + 2;

            default:
                return 0;
        }
    }

    private function handleUserGeneratePractice(string $prompt, array $topic): void
    {
        $content = '';

        $this->streamResponse($prompt, function (string $chunk) use (&$content) {
            $content .= $chunk;
        });

        $chat_id = $this->resolveChat($topic['id'])['id'];

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $systemMsg = Messages::create($chat_id, 'system', 'command', ['text' => 'Amaliy topshiriqlar']);
            if (!$systemMsg) {
                throw new \Exception("System xabari saqlanmadi: chat_id={$chat_id}");
            }

            $mentorMsg = Messages::create($chat_id, 'mentor', 'practice', ['text' => $content]);
            if (!$mentorMsg) {
                throw new \Exception("Mentor amaliy topshirig'i saqlanmadi: chat_id={$chat_id}");
            }

            $updateCount = Chats::updateAll(
                ['current_stage' => new \yii\db\Expression('current_stage + 1')],
                ['id' => $chat_id]
            );

            if ($updateCount === 0) {
                throw new \Exception("Chat holati yangilanmadi: chat_id={$chat_id}");
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();

            Yii::error("handleUserGeneratePractice xatoligi: " . $e->getMessage());
        }
        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    private function handleUserAskQuestion(string $prompt, array $topic, string $user_question): void
    {
        $answer = '';

        $this->streamResponse($prompt, function (string $chunk) use (&$answer) {
            $answer .= $chunk;
        });

        $chat_id = $this->resolveChat($topic['id'])['id'];

        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $userMsg = Messages::create($chat_id, 'user', 'text', ["text" => $user_question]);
            if (!$userMsg) {
                throw new \Exception("User savoli saqlanmadi: chat_id={$chat_id}");
            }

            $mentorMsg = Messages::create($chat_id, 'mentor', 'text', ["text" => $answer]);
            if (!$mentorMsg) {
                throw new \Exception("User savoliga mentor javobi saqlanmadi: chat_id={$chat_id}");
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();

            Yii::error("handleUserAskQuestion xatoligi: " . $e->getMessage());   
        }
        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    private function handleUserCheckPractice(string $prompt, array $topic, array $answers): void
    {
        $feedback = '';

        $this->streamResponse($prompt, function (string $chunk) use (&$feedback) {
            $feedback .= $chunk;
        });

        $chat_id = $this->resolveChat($topic['id'])['id'];

        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($answers as $answer){
                $userMsg = Messages::create($chat_id, 'user', 'text', ['text' => "{$answer['task_number']}-topshiriq javobi: \n {$answer['answer']}"]);
                if (!$userMsg) {
                    throw new \Exception("User xabari saqlanmadi: chat_id={$chat_id}");
                }
            }

            $mentorMsg = Messages::create($chat_id, 'mentor', 'practice_result', ['text' => $feedback]);
            if (!$mentorMsg) {
                throw new \Exception("Mentor amaliy topshiriqni tekshirgani saqlanmadi: chat_id={$chat_id}");
            }

            $updateCount = Chats::updateAll(
                ['current_stage' => new \yii\db\Expression('current_stage + 1')],
                ['id' => $chat_id]
            );

            if ($updateCount === 0) {
                throw new \Exception("Chat holati yangilanmadi: chat_id={$chat_id}");
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();

            Yii::error("handleUserCheckPractice xatoligi: " . $e->getMessage());
        }
        Yii::$app->response->isSent = true;
        Yii::$app->end();
    }

    private function handleUserGenerateQuizTest(string $prompt, array $topic)
    {
        $raw  = (new GeminiApiService())->getContent($prompt);

        if ($raw === 'network error') {
            return $this->asJson(['success' => false, 'message' => 'Tizimda xatolik yuz berdi!']);
        }

        $json = $this->parseJsonResponse($raw);
        if ($json === null) {
            return $this->asJson(['success' => false, 'message' => "Test hosil qilishda xatolik yuz berdi! Iltimos sahifani qayta yuklang"]);
        }

        [$quiz_data, $quiz_clean] = $this->splitQuizData($json['quiz']);

        $content = [];

        for ($i = 0; $i < count($quiz_clean); $i++) {
            $content[$i]["data"] = $quiz_clean[$i];
            $content[$i]["answers"] = $quiz_data[$i];
        }

        $chat_id = $this->resolveChat($topic['id'])['id'];

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $systemMsg = Messages::create($chat_id, 'system', 'command', ["text" => "Quiz testlar"]);
            if (!$systemMsg) {
                throw new \Exception("System xabari saqlanmadi: chat_id={$chat_id}");
            }

            $mentorMsg = Messages::create($chat_id, 'mentor', 'quiz', $content);
            if (!$mentorMsg) {
                throw new \Exception("Mentor quiz testi saqlanmadi: chat_id={$chat_id}");
            }

            $updateCount = Chats::updateAll(
                ['current_stage' => new \yii\db\Expression('current_stage + 1')],
                ['id' => $chat_id]
            );

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();

            Yii::error("handleUserGenerateQuizTest xatoligi: " . $e->getMessage());

            return $this->asJson(['success' => false, 'message' => "Noma'lum xatolik yuz berdi! Iltimos sahifani qayta yuklang"]);
        }

        return $this->asJson(['success' => true, 'data' => $quiz_clean]);
    }

    private function handleUserCheckQuiz(array $selected)
    {
        $body     = Yii::$app->request->getBodyParams();
        $course = $this->resolveCourse();
        $topic = $this->resolveTopic((int) ($body['topic_id'] ?? 0), $course['id'], useJson: true);
        $chat_id = $this->resolveChat($topic['id'])['id'];
        $quiz_content = Messages::find()
            ->select('content')
            ->where(["chat_id" => $chat_id, 'sender_role' => 'mentor', 'type' => 'quiz'])
            ->asArray()
            ->one()['content'];
        $quiz_clean_content = json_decode($quiz_content, true);
        $quiz_answers = [];
        for ($i = 0; $i < count($quiz_clean_content); $i++) {
            $quiz_answers[$i] = $quiz_clean_content[$i]["answers"];
        }

        if (empty($quiz_answers)) {
            return $this->asJson([
                'success' => false,
                'message' => "Testlarni tekshirishda xatolik! Iltimos sahifani qayta yuklang",
            ]);
        }

        $results = $this->buildQuizResults($selected, $quiz_answers);

        for ($i = 0; $i < count($results); $i++) {
            $quiz_clean_content[$i]["user-results"] = [
                "status" => $results[$i]["status"],
                "selected" => $results[$i]["selected"]
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $quiz_msg = Messages::findOne(["chat_id" => $chat_id, "sender_role" => 'mentor', "type" => 'quiz']);
            if (!$quiz_msg) {
                throw new \Exception("Quiz test topilmadi: chat_id={$chat_id}");
            }

            $quiz_msg->content = $quiz_clean_content;
            if (!$quiz_msg->save()) {
                throw new \Exception("Tekshirilgan quiz test saqlanmadi: chat_id={$chat_id}");   
            }

            $updateCount = Chats::updateAll(
                ['current_stage' => new \yii\db\Expression('current_stage + 1')],
                ['id' => $chat_id]
            );

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();

            Yii::error("handleUserCheckQuiz xatoligi: " . $e->getMessage());

            return $this->asJson(['success' => false, 'message' => "Noma'lum xatolik yuz berdi! Iltimos sahifani qayta yuklang"]);
        }

        return $this->asJson([
            'success' => true,
            'data'    => ['results' => $results],
        ]);
    }


    private function requireAjax(): void
    {
        if (!Yii::$app->request->isAjax) {
            $this->goBack();
            exit(0);
        }
    }

    private function resolveCourse(bool $useJson = false)
    {
        if (!Yii::$app->user->isGuest) {
            $course_id = Yii::$app->user->identity->activeData['course_id'];
        } else {
            $course_id = (int) (Yii::$app->request->getBodyParam('course_id') ?? 0);
        }        

        if (!$course_id) {
            $this->failWith("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang", $useJson);
        }

        $course = Courses::find()
            ->select(['id', 'mentor_id', 'name'])
            ->where(['id' => $course_id])
            ->with([
                'mentor' => function ($query) {
                    $query->select(['id', 'name', 'personality']);
                }
            ])
            ->asArray()
            ->one();

        if (!$course) {
            $this->failWith(
                "Bunday kurs mavjud emas. Iltimos sahifani qayta yuklang",
                $useJson
            );
        }

        return $course;
    }

    private function resolveTopic(int $topic_id, int $course_id, bool $useJson = false): array
    {
        if (!$topic_id) {
            $this->failWith("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang", $useJson);
        }

        $topic = Topics::find()
            ->alias('t')
            ->select(['t.id', 't.module_id', 't.type', 't.title', 'key_concepts'])
            ->innerJoin('course_modules cm', 'cm.module_id = t.module_id')
            ->where([
                't.id' => $topic_id,
                'cm.course_id' => $course_id
            ])
            ->asArray()
            ->one();

        if (!$topic) {
            $this->failWith(
                "Bunday mavzu topilmadi! Mavzu kursdan chiqib ketgan bo'lishi mumkin. Iltimos sahifani qayta yuklang",
                $useJson
            );
        }

        return $topic;
    }

    private function resolveLevel(bool $useJson = false): array
    {
        if (!Yii::$app->user->isGuest) {
            $level_id = Yii::$app->user->identity->activeData['level_id'];
        } else {
            $level_id = (int) (Yii::$app->request->getBodyParam('level_id') ?? 0);
        }

        if (!$level_id) {
            $this->failWith("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang", $useJson);
        }

        $level = Levels::find()
            ->select(['title', 'description'])
            ->where(['id' => $level_id])
            ->asArray()
            ->one();

        if (!$level) {
            $this->failWith(
                "Dasturlashdagi darajangiz noto'g'ri tanlangan! Iltimos sahifani qayta yuklang",
                $useJson
            );
        }

        return $level;
    }
 
    private function resolveChat(int $topic_id, array $extra_data = []) {
        $needed_data = array_merge(['id'], $extra_data);
        $data = Chats::find()
            ->select($needed_data)
            ->where([
                'topic_id' => $topic_id,
                "user_data_id" => Yii::$app->user->identity->last_active_user_data_id
            ])
            ->asArray()
            ->one();

        if (!$data) {
            $new_chat = Chats::create($topic_id, 0, $needed_data);

            if (!$new_chat['success']) return false;
            return $new_chat['data'];
        } else {
            return $data;
        }
    }

    private function requireField(array $body, string $field, bool $useJson = false): string
    {
        $value = trim($body[$field] ?? '');

        if ($value === '') {
            $this->failWith("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang", $useJson);
        }

        return $value;
    }

    private function resolvePrompt(string $type, array $params, string $topicType = ''): string
    {
        $prompt = PromptService::getPrompt($type, $params, $topicType);

        if (!$prompt) {
            $this->failWith("Xizmat vaqtinchalik ishlamayapti. Iltimos keyinroq urinib ko'ring!");
        }

        return $prompt;
    }

    private function streamResponse(string $prompt, ?callable $onChunk = null): void
    {
        $gemini_api = new GeminiApiService();
        $gemini_api->prepareSystemForStreaming();

        try {
            $gemini_api->streamContent($prompt, function (string $text) use ($onChunk) {
                echo 'data: ' . json_encode(['content' => $text]) . "\n\n";
                flush();

                if ($onChunk !== null) {
                    $onChunk($text);
                }
            });

            echo "data: [DONE]\n\n";
        } catch (\Throwable $e) {
            $this->sendStreamError($this->resolveStreamErrorMessage($e));
        }
    }

    private function resolveStreamErrorMessage(\Throwable $e): string
    {
        return match ($e->getMessage()) {
            'NETWORK_ERROR' => "Tarmoq xatoligi yuz berdi. Iltimos keyinroq urinib ko'ring!",
            'API_ERROR'     => "Mentor javob bermayapti. Iltimos keyinroq urinib ko'ring!",
            default         => "Noma'lum xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!",
        };
    }

    private function sendStreamError(string $message): never
    {
        echo 'data: ' . json_encode(['error' => $message]) . "\n\n";
        exit(0);
    }

    private function failWith(string $message, bool $useJson = false): never
    {
        if ($useJson) {
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            $this->sendStreamError($message);
        }
        exit(0);
    }

    private function parseJsonResponse(string $raw): ?array
    {
        $clean = preg_replace('/^```json\s*|\s*```$/m', '', trim($raw));
        $json  = json_decode($clean, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $json : null;
    }

    private function splitQuizData(array $quiz): array
    {
        $quiz_data  = [];
        $quiz_clean = [];

        foreach ($quiz as $i => $item) {
            $quiz_data[$i]  = ['correct' => $item['correct'], 'explanation' => $item['explanation']];
            $clean_item = $item;
            unset($clean_item['correct'], $clean_item['explanation']);
            $quiz_clean[$i] = $clean_item;
        }

        return [$quiz_data, $quiz_clean];
    }

    private function buildQuizResults(array $selected, array $quiz_data): array
    {
        $results = [];

        for ($i = 0; $i < 5; $i++) {
            $results[$i]['status'] = 'unkown';
            $results[$i]['correct'] = $quiz_data[$i]['correct'];
            $results[$i]['selected'] = $selected[$i] ?? null;
            $results[$i]['explanation'] = $quiz_data[$i]['explanation'];

            if (!array_key_exists($i, $selected) || $selected[$i] === null) {
                $results[$i]['status']  = 'unanswered';
            } elseif ($quiz_data[$i]['correct'] === $selected[$i]) {
                $results[$i]['status']   = 'correct';
            } else {
                $results[$i]['status']   = 'incorrect';
            }
        }

        return $results;
    }

    private function formatAnswers(array $user_answers): string
    {
        $answers = '';
        foreach ($user_answers as $item) {
            $answers .= "{$item['task_number']}-topshiriq javobi:\n{$item['answer']}\n";
        }
        return $answers;
    }
}
