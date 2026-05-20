<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Mentors;
use app\models\Courses;

class SiteController extends Controller
{
    public $layout = "site";
    
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function actionMain() {
        $mentors = Mentors::getAll();
        $courses = Courses::find()->withStats()->limit(3)->asArray()->all();
        return $this->render("main", [
            'mentors' => $mentors,
            'courses' => $courses
        ]);
    }

    public function actionCourses() {
        $courses = Courses::find()->withStats()->asArray()->all();
        return $this->render("courses", [
            "courses" => $courses
        ]);
    }

    public function actionCoursePreview(int $course_id) {
        $course = Courses::find()
            ->where(['id' => $course_id])
            ->with([
                'mentor' => function ($query) {
                    $query->select(['id', 'name', 'title', 'description', 'skills', 'chat_img']);
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

        return $this->render("course-preview", [
            'course' => $course
        ]);
    }
    
    public function actionAboutUs() {
        return $this->render("about-us");
    }
}