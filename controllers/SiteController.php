<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\SelectedForm;
use app\models\Topics;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['home', 'preview'],
                'rules' => [
                    [
                        'actions' => ['home'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['preview'],
                        'allow' => true,
                        'roles' => ['?']
                    ]
                ],
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function actionProfile() {
        return $this->render("profile");
    }

    public function actionAboutUs() {
        return $this->render("about-us");
    }

    //user actions:
    
    /**
     * Tizimga kirgan foydalanuvchilar uchun home sahifasi
     *
     * @return string
     */
    public function actionHome()
    {
        // return $this->render('home');
    }


    //guest actions:
    
    /**
     * Mehmon foydalanuvchilar uchun home sahifasi
     *
     * @return string
     */
    public function actionPreview(int $course_id, int $level_id) {
        $selected_model = new SelectedForm();
        if (!$selected_model->load(["course_id" => $course_id, "level_id" => $level_id], '') || !$selected_model->validate()) {
            return $this->goBack();
        }
        $data = Topics::getTopicsByCourseId($course_id);
        $category = $data['category'] ?? null;
        $language = $data['language'] ?? null;
        $topics_data = $data['data'];
        if (empty($topics_data)) {
            return $this->goBack();
        }
        return $this->render("home", [
            "category" => $category,
            "language" => $language,
            "level_id" => $level_id,
            "topics_data" => $topics_data
        ]);
    }
}
