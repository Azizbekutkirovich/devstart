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
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['home', 'my-courses', 'profile', 'home-preview', 'my-courses-preview', 'profile-preview'],
                'rules' => [
                    [
                        'actions' => ['home', 'my-courses', 'profile'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['home-preview', 'my-courses-preview', 'profile-preview'],
                        'allow' => true,
                        'roles' => ['?']
                    ]
                ],
            ]
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function actionProfile() {
        return $this->render("profile", [
            'fullname' => Yii::$app->user->identity->fullname,
            'email' => Yii::$app->user->identity->email,
            'email_verified' => Yii::$app->user->identity->email_verified
        ]);
    }

    //user actions:
    
    public function actionHome()
    {
        $user = Yii::$app->user->identity;
        $lastActiveUserDataId = $user->last_active_user_data_id;
        $courseId = $user->activeData['course_id'];
        $levelId = $user->activeData['level_id'];

        $data = Courses::find()
            ->where(['id' => $courseId])
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
                    $query->select(['id', 'module_id', 'title', 'order_weight'])
                        ->orderBy(['order_weight' => SORT_ASC]);
                },
                'modules.topics.chats' => function ($query) use ($lastActiveUserDataId) {
                    $query->select(['id', 'user_data_id', 'topic_id', 'current_stage', 'total_stages'])
                          ->where(['user_data_id' => $lastActiveUserDataId]);
                }
            ])
            ->asArray()
            ->one();

        if (!$data) {
            throw new \yii\web\NotFoundHttpException("Kurs topilmadi");
        }

        $level_data = Levels::find()
            ->where(['id' => $levelId])
            ->select(['id', 'title', 'icon'])
            ->asArray()
            ->one();

        $modules = [];
        foreach ($data['modules'] as $module) {
            $formattedTopics = [];
            foreach ($module['topics'] as $topic) {
                $formattedTopics[] = [
                    'id' => $topic['id'],
                    'title' => $topic['title'],
                    'progress' => round($this->calculateTopicProgress($topic, $lastActiveUserDataId), 1)
                ];
            }

            $modules[] = [
                'name' => $module['name'],
                'topics_count' => count($module['topics']),
                'progress' => round($this->calculateModuleProgress($module, $lastActiveUserDataId), 1),
                'topics' => $formattedTopics
            ];
        }

        return $this->render('home', [
            "fullname" => $user->fullname,
            "level" => [
                "title" => $level_data['title'] ?? 'Noma\'lum',
                "img" => $level_data['icon'] ?? ''
            ],
            "course" => [
                "name" => $data['name'],
                "progress" => $this->calculateCourseProgress($data, $lastActiveUserDataId)
            ],
            "mentor" => [
                "name" => $data['mentor']['name'] ?? 'Mentor',
                "img" => $data['mentor']['chat_img'] ?? ''
            ],
            "modules" => $modules
        ]);
    }

    public function actionMyCourses()
    {
        $user = Yii::$app->user->identity;
        $lastActiveUserDataId = $user->last_active_user_data_id;
        $activeCourseId = $user->activeData['course_id'];
        $activeLevelId = $user->activeData['level_id'];

        $activeCourseData = Courses::find()
            ->where(['id' => $activeCourseId])
            ->with([
                'mentor',
                'modules.topics.chats' => function ($q) use ($lastActiveUserDataId) {
                    $q->where(['user_data_id' => $lastActiveUserDataId]);
                }
            ])
            ->asArray()
            ->one();

        $activeLevelData = Levels::find()->where(['id' => $activeLevelId])->asArray()->one();

        $otherUserData = UserData::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['not', ['id' => $lastActiveUserDataId]])
            ->with([
                'course.mentor', 
                'level',
                'course.modules.topics.chats' => function ($q) {
                    $q->onCondition(['chats.user_data_id' => new \yii\db\Expression('user_data_id')]);
                }
            ])
            ->all();

        $other_courses = [];
        foreach ($otherUserData as $uData) {
            $other_courses[] = [
                "user_data_id" => $uData->id,
                "name" => $uData->course->name,
                "progress" => $this->calculateCourseProgress($uData->course, $uData->id),
                "course_level" => [
                    "title" => $uData->level->title,
                    "img" => $uData->level->icon
                ],
                "mentor" => [
                    "name" => $uData->course->mentor->name,
                    "img" => $uData->course->mentor->chat_img
                ]
            ];
        }

        return $this->render("my-courses", [
            "active_course" => [
                "name" => $activeCourseData['name'] ?? 'Noma\'lum',
                "progress" => $this->calculateCourseProgress($activeCourseData, $lastActiveUserDataId)
            ],
            "active_level" => [
                "title" => $activeLevelData['title'] ?? '',
                "img" => $activeLevelData['icon'] ?? ''
            ],
            "active_mentor" => [
                "name" => $activeCourseData['mentor']['name'] ?? '',
                "img" => $activeCourseData['mentor']['chat_img'] ?? ''
            ],
            "other_courses" => $other_courses
        ]);
    }


    //guest actions:
    
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

    private function calculateTopicProgress($topic, $userDataId)
    {
        $chats = is_array($topic) ? ($topic['chats'] ?? []) : ($topic->chats ?? []);
        
        foreach ($chats as $chat) {
            if ($chat['user_data_id'] == $userDataId) {
                if ($chat['total_stages'] > 0) {
                    return ($chat['current_stage'] / $chat['total_stages']) * 100;
                }
            }
        }
        return 0;
    }

    private function calculateModuleProgress($module, $userDataId)
    {
        $topics = is_array($module) ? ($module['topics'] ?? []) : ($module->topics ?? []);
        $topicsCount = count($topics);
        
        if ($topicsCount === 0) return 0;

        $totalProgress = 0;
        foreach ($topics as $topic) {
            $totalProgress += $this->calculateTopicProgress($topic, $userDataId);
        }

        return $totalProgress / $topicsCount;
    }

    private function calculateCourseProgress($course, $userDataId)
    {
        $modules = is_array($course) ? ($course['modules'] ?? []) : ($course->modules ?? []);
        $modulesCount = count($modules);
        
        if ($modulesCount === 0) return 0;

        $totalProgress = 0;
        foreach ($modules as $module) {
            $totalProgress += $this->calculateModuleProgress($module, $userDataId);
        }

        return round($totalProgress / $modulesCount, 1);
    }
}