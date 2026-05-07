<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\SelectedForm;
use app\models\UserData;
use app\models\Courses;
use app\models\Levels;

class DashboardController extends Controller
{
    public $layout = "dashboard";
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['home', 'home-preview', 'my-courses-preview'],
                'rules' => [
                    [
                        'actions' => ['home'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['home-preview', 'my-courses-preview'],
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

    //user actions:
    
    /**
     * Tizimga kirgan foydalanuvchilar uchun home sahifasi
     *
     * @return string
     */
    public function actionHome()
    {
        $data = Courses::find()
            ->where(['id' => Yii::$app->user->identity->activeData['course_id']])
            ->select(['id', 'name', 'mentor_id'])
            ->with([
                'mentor' => function ($query) {
                    $query->select(['id', 'name', 'chat_img']);
                },
                'modules' => function ($query) {
                    $query->select(['id', 'name'])
                        ->orderBy(['id' => SORT_ASC]); 
                },
                'modules.topics' => function ($query) {
                    $query->select(['id', 'module_id', 'title'])
                        ->orderBy(['order_weight' => SORT_ASC]);
                }
            ])
            ->asArray()
            ->one();
        $level_data = Levels::find()
            ->where(['id' => Yii::$app->user->identity->activeData['course_id']])
            ->select(['id', 'title', 'icon'])
            ->asArray()
            ->one();

        $fullname = Yii::$app->user->identity->fullname;
        $level = [
            "title" => $level_data['title'],
            "img" => $level_data['icon']
        ];
        $course = [
            "name" => $data['name'],
            "progress" => 0
        ];
        $mentor = [
            "name" => $data['mentor']['name'],
            "img" => $data['mentor']['chat_img']
        ];
        $modules = [];
        foreach ($data['modules'] as $module) {
            $topics_count = count($module['topics']);
            $item = [
                'name' => $module['name'],
                'topics_count' => $topics_count,
                'progress' => 0,
                'topics' => []
            ];

            foreach ($module['topics'] as $topic) {
                $item['topics'][] = [
                    'id' => $topic['id'],
                    'title' => $topic['title'],
                    'progress' => 0
                ];
            }

            $modules[] = $item;
        }
        return $this->render('home', [
            "fullname" => $fullname,
            "level" => $level,
            "course" => $course,
            "mentor" => $mentor,
            "modules" => $modules
        ]);
    }


    //guest actions:
    
    /**
     * Mehmon foydalanuvchilar uchun home sahifasi
     *
     * @return string
     */
    public function actionHomePreview(int $course_id, int $level_id) {
        if (!$this->validateGuest($course_id, $level_id)) return $this->goBack();
        $data = Courses::find()
            ->where(['id' => $course_id])
            ->select(['id', 'name', 'mentor_id'])
            ->with([
                'mentor' => function ($query) {
                    $query->select(['id', 'name', 'chat_img']);
                },
                'modules' => function ($query) {
                    $query->select(['id', 'name'])
                        ->orderBy(['id' => SORT_ASC]); 
                },
                'modules.topics' => function ($query) {
                    $query->select(['id', 'module_id', 'title'])
                        ->orderBy(['order_weight' => SORT_ASC]);
                }
            ])
            ->asArray()
            ->one();
        $level_data = Levels::find()
            ->where(['id' => $level_id])
            ->select(['id', 'title', 'icon'])
            ->asArray()
            ->one();

        $fullname = "Mehmon";
        $level = [
            "title" => $level_data['title'],
            "img" => $level_data['icon']
        ];
        $course = [
            "name" => $data['name'],
            "progress" => 0
        ];
        $mentor = [
            "name" => $data['mentor']['name'],
            "img" => $data['mentor']['chat_img']
        ];
        $modules = [];
        foreach ($data['modules'] as $module) {
            $topics_count = count($module['topics']);
            $item = [
                'name' => $module['name'],
                'topics_count' => $topics_count,
                'progress' => 0,
                'topics' => []
            ];

            foreach ($module['topics'] as $topic) {
                $item['topics'][] = [
                    'id' => $topic['id'],
                    'title' => $topic['title'],
                    'progress' => 0
                ];
            }

            $modules[] = $item;
        }

        return $this->render("home", [
            "fullname" => $fullname,
            "level" => $level,
            "course" => $course,
            "mentor" => $mentor,
            "modules" => $modules,
            "level_id" => $level_id
        ]);
    }

    public function actionMyCoursesPreview(int $course_id, int $level_id) {
        if (!$this->validateGuest($course_id, $level_id)) return $this->goBack();
        $data = Courses::find()
            ->where(['id' => $course_id])
            ->select(['id', 'name', 'mentor_id'])
            ->with([
                'mentor' => function ($query) {
                    $query->select(['id', 'name', 'chat_img']);
                },
            ])
            ->asArray()
            ->one();
        $level_data = Levels::find()
            ->where(['id' => $level_id])
            ->select(['id', 'title', 'icon'])
            ->asArray()
            ->one();

        
        $active_course = [
            "name" => $data["name"],
            "progress" => 0
        ];
        $active_level = [
            "title" => $level_data['title'],
            "img" => $level_data['icon']
        ];
        $active_mentor = [
            "name" => $data['mentor']['name'],
            "img" => $data['mentor']['chat_img']
        ];
        $other_courses = [];
        
        return $this->render("my-courses", [
            "active_course" => $active_course,
            "active_level" => $active_level,
            "active_mentor" => $active_mentor,
            "other_courses" => $other_courses
        ]);
    }

    public function actionProfilePreview() {
        return $this->render("profile");
    }


    private function validateGuest(int $course_id, int $level_id) {
        $selected_model = new SelectedForm();
        if (!$selected_model->load(["course_id" => $course_id, "level_id" => $level_id], '') || !$selected_model->validate()) return false;
        return true;
    }
}