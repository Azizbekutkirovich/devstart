<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Topics;
use app\models\Levels;
use app\models\SelectedForm;
use app\services\GeminiApiService;
use app\services\PromptService;

class ChatController extends Controller
{
	/**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['chat', 'chat-preview'],
                'rules' => [
                    [
                        'actions' => ['chat'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                    	'actions' => ['chat-preview'],
                    	'allow' => true,
                    	'roles' => ['?']
                    ]
                ],
            ]
        ];
    }

    public function actionGenerateTopic() {
        if (!Yii::$app->request->isAjax) {
            return $this->goBack();
        }
        $body = Yii::$app->request->getBodyParams();

        $topic_id = ($body['topic_id'] ?? 0);

        if (!$topic_id) {
            echo "data: ".json_encode(["error" => "Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang"])."\n\n";
            exit(0);
        }

        $topic = Topics::getTopicById($topic_id);
        if (!$topic) {
                echo "data: ".json_encode(["error" => "Bunday mavzu topilmadi! Mavzu mavzular ro'yxatidan chiqib ketgan bo'lishi mumkin. Iltimos sahifani qayta yuklang"])."\n\n";
            exit(0);
        }
        if (!Yii::$app->user->isGuest) {
            
        } else {
            $level_id = $body["level_id"];
            $level = Levels::getLevelById($level_id);
            if (!$level) {
                echo "data: ".json_encode(["error" => "Dasturlashdagi darajangiz noto'g'ri tanlangan! Iltimos sahifani qayta yuklang"])."\n\n";
                exit(0);
            }

            $prompt = PromptService::getPrompt("generate-topic", ["category" => $topic['category'], "language" => $topic['language'], "level" => $level['name'], "topic_name" => $topic['title']], $topic['type']);

            if (!$prompt) {
                echo "data: ".json_encode(["error" => "Prompt sozlamalarida xatolik mavjud!"])."\n\n";
                exit(0);
            }

            $gemini_api = new GeminiApiService();
            $gemini_api->prepareSystemForStreaming();
            try {
                $gemini_api->streamContent($prompt, function($text) use ($gemini_api) {
                    echo "data: ".json_encode(["content" => $text])."\n\n";
                    flush();
                });
                echo 'data: [DONE]\n\n';
            } catch (\Throwable $e) {
                $error_message = "";
                if ($e->getMessage() === "NETWORK_ERROR") {
                    $error_message = "Tarmoq xatoligi yuz berdi. Iltimos keyinroq urinib ko'ring!";
                } else if ($e->getMessage() === "API_ERROR") {
                    $error_message = "Bot javob bermayapti. Iltimos keyinroq urinib ko'ring!";
                } else {
                    $error_message = "Noma'lum xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!";
                }
                echo "data: ".json_encode(["error" => $error_message])."\n\n";
            }
        }
        exit(0);
    }

    public function actionGenerateQuizTest() {
        if (!Yii::$app->request->isAjax) {
            return $this->goBack();
        }
        $body = Yii::$app->request->getBodyParams();
        
        $topic_id = (int) ($body['topic_id'] ?? 0);
        $lesson_content = trim($body['lesson_content'] ?? '');

        if (!$topic_id || $lesson_content === '') {
            return $this->asJson([
                'success' => false,
                'message' => "Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang",
            ]);
        }

        $topic = Topics::getTopicById($topic_id);
        if ($topic == 'not found') {
            return $this->asJson([
                'success' => false,
                'message' => "Bunday mavzu topilmadi! Mavzu mavzular ro'yxatidan chiqib ketgan bo'lishi mumkin. Iltimos sahifani qayta yuklang",
            ]);
        }

        if (!Yii::$app->user->isGuest) {
            // user
        } else {
            // guest
            $level = (int) ($body['level'] ?? -1);
            $selected_model = new SelectedForm();
            $selected_model->level = $level;
            $selected_model->scenario = "scenarioWithChat";
            if (!$selected_model->validate()) {
                return $this->asJson([
                    'success' => false,
                    'message' => "Dasturlashdagi darajangiz noto'g'ri tanlangan! Iltimos sahifani qayta yuklang",
                ]);
            }

            $gemini_api = new GeminiApiService();
            $prompt = PromptService::getPrompt("generate-quiz-test", ["lesson_content" => $lesson_content, "category" => $topic['category'], "language" => $topic['language'], "level" => $level, "topic_name" => $topic['name']]);
            $data = $gemini_api->getContent($prompt);
            if ($data == "network error") {
                return $this->asJson([
                    "success" => false,
                    "message" => "Tizimda xatolik yuz berdi!"
                ]);
            }
            $data = trim($data);
            $data = preg_replace('/^```json\s*|\s*```$/m', '', $data);
            $json = json_decode($data, true);
            $quiz_test_data = [];
            foreach ($json['quiz'] as $i => $value) {
                $quiz_test_data[$i]["correct"] = $value["correct"];
                $quiz_test_data[$i]["explanation"] = $value["explanation"];
                $value["correct"] = "";
                $value["explanation"] = "";
            }
            Yii::$app->session->setFlash("quiz_test_data", $quiz_test_data);
            return $this->asJson([
                "success" => true,
                "data" => $json['quiz'],
            ]);
        }
    }

    public function actionGeneratePractice() {
        if (!Yii::$app->request->isAjax) {
            return $this->goBack();
        }
        $body = Yii::$app->request->getBodyParams();
        
        $topic_id = (int) ($body['topic_id'] ?? 0);
        $lesson_content = trim($body['lesson_content'] ?? '');

        if (!$topic_id || $lesson_content === '') {
            echo "data: ".json_encode(["error" => "Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang"])."\n\n";
            exit(0);
        }

        $topic = Topics::getTopicById($topic_id);
        if ($topic == 'not found') {
            echo "data: ".json_encode(["error" => "Bunday mavzu topilmadi! Mavzu mavzular ro'yxatidan chiqib ketgan bo'lishi mumkin. Iltimos sahifani qayta yuklang"])."\n\n";
            exit(0);   
        }

        if (!Yii::$app->user->isGuest) {
            // user
        } else {
            // guest
            $level = (int) ($body['level'] ?? -1);
            $selected_model = new SelectedForm();
            $selected_model->level = $level;
            $selected_model->scenario = "scenarioWithChat";
            if (!$selected_model->validate()) {
                echo "data: ".json_encode(["error" => "Dasturlashdagi darajangiz noto'g'ri tanlangan! Iltimos sahifani qayta yuklang"])."\n\n";
                exit(0);
            }
            
            $prompt = PromptService::getPrompt("generate-practice", ["lesson_content" => $lesson_content, "category" => $topic['category'], "language" => $topic['language'], "level" => $level, "topic_name" => $topic['name']]);
            
            $gemini_api = new GeminiApiService();
            $gemini_api->prepareSystemForStreaming();
            try {
                $gemini_api->streamContent($prompt, function($text) use ($gemini_api) {
                    echo "data: ".json_encode(["content" => $text])."\n\n";
                    flush();
                });
                echo 'data: [DONE]\n\n';
            } catch (\Throwable $e) {
                $error_message = "";
                if ($e->getMessage() === "NETWORK_ERROR") {
                    $error_message = "Tarmoq xatoligi yuz berdi. Iltimos keyinroq urinib ko'ring!";
                } else if ($e->getMessage() === "API_ERROR") {
                    $error_message = "Bot javob bermayapti. Iltimos keyinroq urinib ko'ring!";
                } else {
                    $error_message = "Noma'lum xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!";
                }
                echo "data: ".json_encode(["error" => $error_message])."\n\n";
            }
        }
        exit(0);
    }

    public function actionAskQuestionAboutTopic() {
        if (!Yii::$app->request->isAjax) {
            return $this->goBack();
        }
        $body = Yii::$app->request->getBodyParams();
        
        $topic_id = (int) ($body['topic_id'] ?? 0);
        $user_question = trim($body['user_question'] ?? '');

        if (!$topic_id || $user_question === '') {
            echo "data: ".json_encode(["error" => "Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang"])."\n\n";
            exit(0);
        }

        $topic = Topics::getTopicById($topic_id);
        if ($topic == 'not found') {
            echo "data: ".json_encode(["error" => "Bunday mavzu topilmadi! Mavzu mavzular ro'yxatidan chiqib ketgan bo'lishi mumkin. Iltimos sahifani qayta yuklang"])."\n\n";
            exit(0);
        }

        if (!Yii::$app->user->isGuest) {
            // user

        } else {
            //guest
            $level = (int) ($body['level'] ?? -1);
            $selected_model = new SelectedForm();
            $selected_model->level = $level;
            $selected_model->scenario = "scenarioWithChat";
            if (!$selected_model->validate()) {
                echo "data: ".json_encode(["error" => "Dasturlashdagi darajangiz noto'g'ri tanlangan! Iltimos sahifani qayta yuklang"])."\n\n";
                exit(0);
            }

            $prompt = PromptService::getPrompt("ask-question-about-topic", ["category" => $topic['category'], "language" => $topic['language'], "level" => $level, "topic_name" => $topic['name'], "user_question" => $user_question]);
            $gemini_api = new GeminiApiService();
            $gemini_api->prepareSystemForStreaming();
            try {
                $gemini_api->streamContent($prompt, function($text) use ($gemini_api) {
                    echo "data: ".json_encode(["content" => $text])."\n\n";
                    flush();
                });
                echo 'data: [DONE]\n\n';
            } catch (\Throwable $e) {
                $error_message = "";
                if ($e->getMessage() === "NETWORK_ERROR") {
                    $error_message = "Tarmoq xatoligi yuz berdi. Iltimos keyinroq urinib ko'ring!";
                } else if ($e->getMessage() === "API_ERROR") {
                    $error_message = "Bot javob bermayapti. Iltimos keyinroq urinib ko'ring!";
                } else {
                    $error_message = "Noma'lum xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!";
                }
                echo "data: ".json_encode(["error" => $error_message])."\n\n";
            }
        }
        exit(0);
    }

    public function actionCheckQuiz() {
        if (!Yii::$app->request->isAjax) {
            return $this->goBack();
        }
        $body = Yii::$app->request->getBodyParams();
        $selected = $body['selected'] ?? '';
        if ($selected == '') {
            return $this->asJson([
                'success' => false,
                'message' => "Siz tanlagan javoblar kelmadi! Iltimos sahifani qayta yuklang",
            ]);
        }
        $quiz_data = Yii::$app->session->getFlash("quiz_test_data");
        if (!Yii::$app->user->isGuest) {
            // user
        } else {
            //guest
            if (empty($quiz_data)) {
                return $this->asJson([
                    "success" => false,
                    "message" => "Testlarni tekshirishda xatolik! Iltimos sahifani qayta yuklang"
                ]);
            }
        }
        $results = [];
        for ($i = 0; $i < 5; $i++) {
            if (!array_key_exists($i, $selected) || $selected[$i] === null) {
                $results[$i]["status"] = "unanswered";
                $results[$i]["correct"] = $quiz_data[$i]["correct"];
            } else if ($quiz_data[$i]["correct"] == $selected[$i]) {
                $results[$i]["status"] = "correct";
                $results[$i]["selected"] = $selected[$i];
            } else {
                $results[$i]["status"] = "incorrect";
                $results[$i]["correct"] = $quiz_data[$i]["correct"];
                $results[$i]["selected"] = $selected[$i];
            }
            $results[$i]["explanation"] = $quiz_data[$i]["explanation"];
        }
        return $this->asJson([
            "success" => true,
            "data" => [
                "results" => $results
            ]
        ]);
    }

    public function actionCheckPractice() {
        if (!Yii::$app->request->isAjax) {
            return $this->goBack();
        }
        $body = Yii::$app->request->getBodyParams();
        
        $practices = trim($body['practices'] ?? '');
        $user_answers = $body['answers'] ?? '';

        if ($user_answers === '' || $practices === '') {
            echo "data: ".json_encode(["error" => "Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang"])."\n\n";
            exit(0);
        }

        if (!Yii::$app->user->isGuest) {
            // user

        } else {
            // guest
            $topic_id = (int) ($body['topic_id'] ?? 0);

            if (!$topic_id) {
                echo "data: ".json_encode(["error" => "Ma'lumotlar to'liq emas! Iltimos sahifani qayta yuklang"])."\n\n";
                exit(0);
            }

            $topic = Topics::getTopicById($topic_id);
            if ($topic == 'not found') {
                echo "data: ".json_encode(["error" => "Bunday mavzu topilmadi! Mavzu mavzular ro'yxatidan chiqib ketgan bo'lishi mumkin. Iltimos sahifani qayta yuklang"])."\n\n";
                exit(0);
            }

            $level = (int) ($body['level'] ?? -1);
            $selected_model = new SelectedForm();
            $selected_model->level = $level;
            $selected_model->scenario = "scenarioWithChat";
            if (!$selected_model->validate()) {
                echo "data: ".json_encode(["error" => "Dasturlashdagi darajangiz noto'g'ri tanlangan! Iltimos sahifani qayta yuklang"])."\n\n";
                exit(0);
            }

            $answers = "";
            foreach ($user_answers as $key => $value) {
                $answers .= "{$value['task_number']}-topshiriq javobi: \n";
                $answers .= $value["answer"];
            }

            $prompt = PromptService::getPrompt("check-practice", ["category" => $topic['category'], "language" => $topic['language'], "level" => $level, "practices" => $practices, "user_answers" => $answers]);

            $gemini_api = new GeminiApiService();
            $gemini_api->prepareSystemForStreaming();
            try {
                $gemini_api->streamContent($prompt, function($text) use ($gemini_api) {
                    echo "data: ".json_encode(["content" => $text])."\n\n";
                    flush();
                });
                echo 'data: [DONE]\n\n';
            } catch (\Throwable $e) {
                $error_message = "";
                if ($e->getMessage() === "NETWORK_ERROR") {
                    $error_message = "Tarmoq xatoligi yuz berdi. Iltimos keyinroq urinib ko'ring!";
                } else if ($e->getMessage() === "API_ERROR") {
                    $error_message = "Bot javob bermayapti. Iltimos keyinroq urinib ko'ring!";
                } else {
                    $error_message = "Noma'lum xatolik yuz berdi. Iltimos keyinroq urinib ko'ring!";
                }
                echo "data: ".json_encode(["error" => $error_message])."\n\n";
            }
        }
        exit(0);
    }

    //guest actions:
    
    /**
     * Mehmon foydalanuvchilar uchun chat sahifasi
     *
     * @return string
    */
    public function actionChatPreview(int $topic_id, int $level_id) {
    	$topic = Topics::getTopicById($topic_id);
    	$selected_model = new SelectedForm();
    	$selected_model->level_id = $level_id;
    	$selected_model->scenario = "scenarioWithChat";
    	if (!isset($topic) || !$selected_model->validate()) {
    		return $this->goBack();
    	}
    	return $this->render("chat", [
    		"topic_name" => $topic['title']
    	]);
    }
}