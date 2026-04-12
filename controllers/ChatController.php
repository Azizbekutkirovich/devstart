<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Topics;
use app\models\Levels;
use app\services\GeminiApiService;
use app\services\PromptService;

/**
 * ChatController
 *
 * Har bir action ikki rejimda ishlaydi:
 *  - Guest : level_id request body'dan olinadi, natija saqlanmaydi.
 *  - User  : level_id session'dan olinadi, natija DB'ga saqlanadi (TODO).
 *
 * Kelajakdagi DB integratsiyasi uchun har bir actionda
 * `handleUser*()` va `handleGuest*()` juftligi mavjud.
 */
class ChatController extends Controller
{
    // =========================================================================
    // Behaviors
    // =========================================================================

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

    // =========================================================================
    // Actions — Streaming (SSE)
    // =========================================================================

    public function actionGenerateTopic(): void
    {
        $this->requireAjax();

        $body  = Yii::$app->request->getBodyParams();
        $topic = $this->resolveTopic((int) ($body['topic_id'] ?? 0));
        $level = $this->resolveLevel();                          // user→session | guest→body

        $prompt = $this->resolvePrompt('generate-topic', [
            'category'   => $topic['category'],
            'language'   => $topic['language'],
            'level'      => $level['name'],
            'topic_name' => $topic['title'],
        ], $topic['type']);

        if (Yii::$app->user->isGuest) {
            $this->handleGuestGenerateTopic($prompt);
        } else {
            $this->handleUserGenerateTopic($prompt, $topic, $level);
        }
    }

    public function actionGeneratePractice(): void
    {
        $this->requireAjax();

        $body           = Yii::$app->request->getBodyParams();
        $topic          = $this->resolveTopic((int) ($body['topic_id'] ?? 0));
        $level          = $this->resolveLevel();
        $lesson_content = $this->requireField($body, 'lesson_content');

        $prompt = $this->resolvePrompt('generate-practice', [
            'lesson_content' => $lesson_content,
            'category'       => $topic['category'],
            'language'       => $topic['language'],
            'level'          => $level['name'],
            'topic_name'     => $topic['title'],
        ]);

        if (Yii::$app->user->isGuest) {
            $this->handleGuestGeneratePractice($prompt);
        } else {
            $this->handleUserGeneratePractice($prompt, $topic, $level);
        }
    }

    public function actionAskQuestionAboutTopic(): void
    {
        $this->requireAjax();

        $body          = Yii::$app->request->getBodyParams();
        $topic         = $this->resolveTopic((int) ($body['topic_id'] ?? 0));
        $level         = $this->resolveLevel();
        $user_question = $this->requireField($body, 'user_question');

        $prompt = $this->resolvePrompt('ask-question-about-topic', [
            'category'      => $topic['category'],
            'language'      => $topic['language'],
            'level'         => $level['name'],
            'topic_name'    => $topic['title'],
            'user_question' => $user_question,
        ]);

        if (Yii::$app->user->isGuest) {
            $this->handleGuestAskQuestion($prompt);
        } else {
            $this->handleUserAskQuestion($prompt, $topic, $level, $user_question);
        }
    }

    public function actionCheckPractice(): void
    {
        $this->requireAjax();

        $body         = Yii::$app->request->getBodyParams();
        $topic        = $this->resolveTopic((int) ($body['topic_id'] ?? 0));
        $level        = $this->resolveLevel();
        $practices    = $this->requireField($body, 'practices');
        $user_answers = $body['answers'] ?? [];

        if (empty($user_answers)) {
            $this->sendStreamError("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang");
        }

        $prompt = $this->resolvePrompt('check-practice', [
            'category'     => $topic['category'],
            'language'     => $topic['language'],
            'level'        => $level['name'],
            'practices'    => $practices,
            'user_answers' => $this->formatAnswers($user_answers),
        ]);

        if (Yii::$app->user->isGuest) {
            $this->handleGuestCheckPractice($prompt);
        } else {
            $this->handleUserCheckPractice($prompt, $topic, $level);
        }
    }

    // =========================================================================
    // Actions — JSON
    // =========================================================================

    public function actionGenerateQuizTest()
    {
        $this->requireAjax();

        $body           = Yii::$app->request->getBodyParams();
        $topic          = $this->resolveTopic((int) ($body['topic_id'] ?? 0), useJson: true);
        $level          = $this->resolveLevel(useJson: true);
        $lesson_content = $this->requireField($body, 'lesson_content', useJson: true);

        $prompt = $this->resolvePrompt('generate-quiz-test', [
            'lesson_content' => $lesson_content,
            'category'       => $topic['category'],
            'language'       => $topic['language'],
            'level'          => $level['name'],
            'topic_name'     => $topic['title'],
        ]);

        if (Yii::$app->user->isGuest) {
            return $this->handleGuestGenerateQuizTest($prompt);
        } else {
            return $this->handleUserGenerateQuizTest($prompt, $topic, $level);
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

    // =========================================================================
    // Actions — Pages
    // =========================================================================

    /**
     * Mehmon foydalanuvchilar uchun chat sahifasi.
     */
    public function actionChatPreview(int $topic_id, int $level_id): string
    {
        $topic = Topics::getTopicById($topic_id);
        $level = Levels::getLevelById($level_id);

        if (!$topic || !$level) {
            return $this->goBack();
        }

        return $this->render('chat', [
            'topic_name' => $topic['title'],
            'topic_type' => $topic['type']
        ]);
    }

    // =========================================================================
    // Guest handlers
    //
    // Faqat AI dan javob olib streamlaydi yoki JSON qaytaradi.
    // DB bilan ishi yo'q.
    // =========================================================================

    private function handleGuestGenerateTopic(string $prompt): void
    {
        $this->streamResponse($prompt);
    }

    private function handleGuestGeneratePractice(string $prompt): void
    {
        $this->streamResponse($prompt);
    }

    private function handleGuestAskQuestion(string $prompt): void
    {
        $this->streamResponse($prompt);
    }

    private function handleGuestCheckPractice(string $prompt): void
    {
        $this->streamResponse($prompt);
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

    // =========================================================================
    // User handlers
    //
    // TODO: Har bir handler'da DB saqlash logikasini qo'shish kerak.
    //
    // Yozilishi kerak bo'lgan modellar (taxminiy):
    //   - UserLesson        : foydalanuvchi ko'rgan darslar
    //   - UserPractice      : bajarilgan amaliyotlar va baholar
    //   - UserQuestion      : berilgan savollar va javoblar
    //   - UserQuizResult    : test natijalari (to'g'ri/noto'g'ri/javobsiz)
    //
    // Har bir handler'dagi "TODO" kommentlarini o'sha model tayyor bo'lganda
    // almashtirib chiqish kifoya.
    // =========================================================================

    private function handleUserGenerateTopic(string $prompt, array $topic, array $level): void
    {
        $content = '';

        $this->streamResponse($prompt, function (string $chunk) use (&$content) {
            $content .= $chunk;
        });

        // TODO: UserLesson::saveOrUpdate([
        //     'user_id'  => Yii::$app->user->id,
        //     'topic_id' => $topic['id'],
        //     'level_id' => $level['id'],
        //     'content'  => $content,
        // ]);
    }

    private function handleUserGeneratePractice(string $prompt, array $topic, array $level): void
    {
        $content = '';

        $this->streamResponse($prompt, function (string $chunk) use (&$content) {
            $content .= $chunk;
        });

        // TODO: UserPractice::create([
        //     'user_id'   => Yii::$app->user->id,
        //     'topic_id'  => $topic['id'],
        //     'level_id'  => $level['id'],
        //     'practices' => $content,
        //     'status'    => UserPractice::STATUS_PENDING,
        // ]);
    }

    private function handleUserAskQuestion(string $prompt, array $topic, array $level, string $user_question): void
    {
        $answer = '';

        $this->streamResponse($prompt, function (string $chunk) use (&$answer) {
            $answer .= $chunk;
        });

        // TODO: UserQuestion::create([
        //     'user_id'   => Yii::$app->user->id,
        //     'topic_id'  => $topic['id'],
        //     'level_id'  => $level['id'],
        //     'question'  => $user_question,
        //     'answer'    => $answer,
        // ]);
    }

    private function handleUserCheckPractice(string $prompt, array $topic, array $level): void
    {
        $feedback = '';

        $this->streamResponse($prompt, function (string $chunk) use (&$feedback) {
            $feedback .= $chunk;
        });

        // TODO: UserPractice::updateFeedback([
        //     'user_id'  => Yii::$app->user->id,
        //     'topic_id' => $topic['id'],
        //     'feedback' => $feedback,
        //     'status'   => UserPractice::STATUS_CHECKED,
        // ]);
    }

    private function handleUserGenerateQuizTest(string $prompt, array $topic, array $level): array
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

        // TODO: Sessiondan DB'ga ko'chirish kerak:
        // UserQuiz::createPending([
        //     'user_id'   => Yii::$app->user->id,
        //     'topic_id'  => $topic['id'],
        //     'level_id'  => $level['id'],
        //     'quiz_data' => $quiz_data,
        // ]);
        Yii::$app->session->setFlash('quiz_test_data', $quiz_data); // vaqtinchalik

        return $this->asJson(['success' => true, 'data' => $quiz_clean]);
    }

    private function handleUserCheckQuiz(array $selected): array
    {
        // TODO: $quiz_data = UserQuiz::getPendingByUserId(Yii::$app->user->id);
        $quiz_data = Yii::$app->session->getFlash('quiz_test_data'); // vaqtinchalik

        if (empty($quiz_data)) {
            return $this->asJson([
                'success' => false,
                'message' => "Testlarni tekshirishda xatolik! Iltimos sahifani qayta yuklang",
            ]);
        }

        $results = $this->buildQuizResults($selected, $quiz_data);

        // TODO: UserQuiz::saveResults([
        //     'user_id' => Yii::$app->user->id,
        //     'results' => $results,
        // ]);

        return $this->asJson([
            'success' => true,
            'data'    => ['results' => $results],
        ]);
    }

    // =========================================================================
    // Private helpers — Validation & Resolution
    // =========================================================================

    private function requireAjax(): void
    {
        if (!Yii::$app->request->isAjax) {
            $this->goBack();
            exit(0);
        }
    }

    /**
     * Topic_id bo'yicha mavzuni qaytaradi.
     */
    private function resolveTopic(int $topic_id, bool $useJson = false): array
    {
        if (!$topic_id) {
            $this->failWith("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang", $useJson);
        }

        $topic = Topics::getTopicById($topic_id);

        if (!$topic) {
            $this->failWith(
                "Bunday mavzu topilmadi! Mavzu mavzular ro'yxatidan chiqib ketgan bo'lishi mumkin. Iltimos sahifani qayta yuklang",
                $useJson
            );
        }

        return $topic;
    }

    /**
     * Darajani qaytaradi.
     *  - Tizimga kirgan foydalanuvchi → session'dan oladi.
     *  - Mehmon                        → request body'dan oladi.
     */
    private function resolveLevel(bool $useJson = false): array
    {
        if (!Yii::$app->user->isGuest) {
            // TODO: Yii::$app->user->identity->level_id — profil tayyor bo'lgandan keyin
            $level_id = (int) Yii::$app->session->get('level_id', 0);
        } else {
            $level_id = (int) (Yii::$app->request->getBodyParam('level_id') ?? 0);
        }

        if (!$level_id) {
            $this->failWith("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang", $useJson);
        }

        $level = Levels::getLevelById($level_id);

        if (!$level) {
            $this->failWith(
                "Dasturlashdagi darajangiz noto'g'ri tanlangan! Iltimos sahifani qayta yuklang",
                $useJson
            );
        }

        return $level;
    }

    /**
     * So'rov tanasidan majburiy string maydonni oladi.
     */
    private function requireField(array $body, string $field, bool $useJson = false): string
    {
        $value = trim($body[$field] ?? '');

        if ($value === '') {
            $this->failWith("Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang", $useJson);
        }

        return $value;
    }

    /**
     * PromptService orqali prompt matnini oladi.
     */
    private function resolvePrompt(string $type, array $params, string $topicType = ''): string
    {
        $prompt = PromptService::getPrompt($type, $params, $topicType);

        if (!$prompt) {
            $this->failWith("Xizmat vaqtinchalik ishlamayapti. Iltimos keyinroq urinib ko'ring!");
        }

        return $prompt;
    }

    // =========================================================================
    // Private helpers — Response
    // =========================================================================

    /**
     * SSE orqali streaming javobini yuboradi.
     *
     * @param callable|null $onChunk  Har bir chunk uchun callback (user logikasi uchun).
     */
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

        exit(0);
    }

    private function resolveStreamErrorMessage(\Throwable $e): string
    {
        return match ($e->getMessage()) {
            'NETWORK_ERROR' => "Tarmoq xatoligi yuz berdi. Iltimos keyinroq urinib ko'ring!",
            'API_ERROR'     => "Bot javob bermayapti. Iltimos keyinroq urinib ko'ring!",
            default         => "Noma'lum xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!",
        };
    }

    private function sendStreamError(string $message): never
    {
        echo 'data: ' . json_encode(['error' => $message]) . "\n\n";
        exit(0);
    }

    /**
     * Xato holatida response turига qarab to'g'ri format bilan chiqadi.
     */
    private function failWith(string $message, bool $useJson = false): never
    {
        if ($useJson) {
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            $this->sendStreamError($message);
        }
        exit(0);
    }

    // =========================================================================
    // Private helpers — Business logic
    // =========================================================================

    /**
     * Gemini'dan kelgan xom matnni JSON arrayga aylantiradi.
     */
    private function parseJsonResponse(string $raw): ?array
    {
        $clean = preg_replace('/^```json\s*|\s*```$/m', '', trim($raw));
        $json  = json_decode($clean, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $json : null;
    }

    /**
     * Quiz massivini ikki qismga ajratadi:
     *   [0] $quiz_data  — to'g'ri javob + izoh  (DB/session'ga saqlanadi)
     *   [1] $quiz_clean — foydalanuvchiga boradi (to'g'ri javob yashirilgan)
     */
    private function splitQuizData(array $quiz): array
    {
        $quiz_data  = [];
        $quiz_clean = [];

        foreach ($quiz as $i => $item) {
            $quiz_data[$i]  = ['correct' => $item['correct'], 'explanation' => $item['explanation']];
            $quiz_clean[$i] = array_merge($item, ['correct' => '', 'explanation' => '']);
        }

        return [$quiz_data, $quiz_clean];
    }

    /**
     * Tanlangan javoblarni to'g'ri javoblar bilan solishtiradi.
     */
    private function buildQuizResults(array $selected, array $quiz_data): array
    {
        $results = [];

        for ($i = 0; $i < 5; $i++) {
            $results[$i]['explanation'] = $quiz_data[$i]['explanation'];

            if (!array_key_exists($i, $selected) || $selected[$i] === null) {
                $results[$i]['status']  = 'unanswered';
                $results[$i]['correct'] = $quiz_data[$i]['correct'];
            } elseif ($quiz_data[$i]['correct'] === $selected[$i]) {
                $results[$i]['status']   = 'correct';
                $results[$i]['selected'] = $selected[$i];
            } else {
                $results[$i]['status']   = 'incorrect';
                $results[$i]['correct']  = $quiz_data[$i]['correct'];
                $results[$i]['selected'] = $selected[$i];
            }
        }

        return $results;
    }

    /**
     * Foydalanuvchi javoblar massivini satr formatiga o'tkazadi.
     */
    private function formatAnswers(array $user_answers): string
    {
        $answers = '';
        foreach ($user_answers as $item) {
            $answers .= "{$item['task_number']}-topshiriq javobi:\n{$item['answer']}\n";
        }
        return $answers;
    }
}
