<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Levels;
use app\models\Courses;
use app\models\UserData;
use app\models\RegisterForm;
use app\models\SelectedForm;
use app\models\LoginForm;
use app\services\AuthService;

class AuthController extends Controller
{
    public $layout = "site";

	public function behaviors() {
		return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['login', 'logout', 'register', 'google-callback', 'google-redirect', 'add-course', 'change-course'],
                'rules' => [
                    [
                        'actions' => ['add-course', 'change-course', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login', 'register', 'google-callback', 'google-redirect'],
                        'allow' => true,
                        'roles' => ['?'],
                    ]
                ],
            ]
        ];
	}

    public function actionLogout() {
        Yii::$app->user->logout();
        return $this->goBack();
    }

    public function actionAddCourse(int $course_id, int $level_id) {
        $selected_model = new SelectedForm();
        $selected_model->course_id = $course_id;
        $selected_model->level_id = $level_id;
        if (!$selected_model->validate()) {
            return $this->goBack();
        }
        $user_data = UserData::create(Yii::$app->user->identity->id, $course_id, $level_id);
        if (!$user_data['success']) {
            return $this->goBack();
        }

        $user = Yii::$app->user->identity;
        $user->last_active_user_data_id = $user_data['id'];
        if ($user->save()) {
            return $this->redirect(['dashboard/home']);
        } else {
            Yii::error("User course qo'shganda xatolik: ".$user->getErrors());
            return $this->redirect(['dashboard/my-courses']);
        }
    }

    public function actionChangeCourse($user_data_id)
    {
        $user = Yii::$app->user->identity;
        
        $userData = UserData::findOne(['id' => $user_data_id, 'user_id' => $user->id]);
        
        if ($userData) {
            $user->last_active_user_data_id = $user_data_id;
            if ($user->save(false)) {
                return $this->redirect(['dashboard/home']);
            }
        }
        
        return $this->goBack();
    }

	public function actionStart(int $course_id) {
        $course_name = Courses::find()->select(['name'])->where(['id' => $course_id])->asArray()->one()['name'];
        $levels = Levels::getAll();

        if (!Yii::$app->user->isGuest) {
            return $this->render('start-user', [
                "levels" => $levels,
                "course_name" => $course_name,
                "fullname" => Yii::$app->user->identity->fullname
            ]);
        } else {
            $model = new RegisterForm();
            return $this->render("start", [
                "model" => $model,
                "levels" => $levels,
                "course_name" => $course_name
            ]);
        }
	}

    public function actionLogin() {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login('form')) {
            return $this->redirect(['dashboard/home']);
        }

        return $this->render("login", compact("model"));
    }

    public function actionRegister() {
        if (!Yii::$app->request->isAjax) {
            return $this->goBack();
        }
        $model = new RegisterForm();
        $selected_model = new SelectedForm();

        $post = Yii::$app->request->post();
        if ($model->load($post) && $selected_model->load($post) && $model->register("form", $selected_model)) {
            $auth = new AuthService();

            if ($auth->autoLoginByEmail($model->email)) {
                return $this->asJson([
                    'success' => true
                ]);
            } else {
                return $this->asJson([
                    'success' => false,
                    'errors' => [
                        'login' => "Siz muvaffaqiyatli roʻyxatdan oʻtdingiz, ammo akkauntga kirishda muammo yuz berdi. Iltimos, <a href='/devstart/auth/login'>Kirish</a> havolasi orqali akkauntingizga kiring" 
                    ]
                ]);
            }
        } else {
            return $this->asJson([
                'success' => false,
                'errors' => array_merge_recursive(
                    $selected_model->getErrors(),
                    $model->getErrors()
                ),
                'html' => $this->renderAjax("register", compact("model"))
            ]);
        }
    }

    public function actionGoogleCallback() {
        $client = Yii::$app->authClientCollection->getClient('google');
        $code = Yii::$app->request->get("code");
        $token = $client->fetchAccessToken($code);
        $attributes = $client->getUserAttributes();
        $selected = Yii::$app->session->get("selected");
        if (!empty($selected)) {
            //register
            $model = new RegisterForm();
            $selected_model = new SelectedForm();

            $model->fullname = $attributes['name'];
            $model->email = $attributes['email'];
            $course_id = $selected['course_id'] ?? null;
            if ($selected_model->load($selected, '') && $model->register("google", $selected_model, $attributes['id'])) {
                Yii::$app->session->remove("selected");
                $auth = new AuthService();
                if ($auth->autoLoginByEmail($model->email)) {
                    return $this->redirect(['dashboard/home']);
                } else {
                    $error = "Siz muvaffaqiyatli roʻyxatdan oʻtdingiz, ammo akkauntga kirishda muammo yuz berdi. Iltimos, <a href='/devstart/auth/login'>Kirish</a> havolasi orqali akkauntingizga kiring";
                    Yii::$app->session->setFlash("google-register-errors", ["login" => ["$error"]]);
                    return $this->redirect(['auth/start', 'course_id' => $course_id]);
                }
            } else {
                Yii::$app->session->remove("selected");
                Yii::$app->session->setFlash("google-register-errors", array_merge_recursive($selected_model->getErrors(), $model->getErrors()));
                return $this->redirect(['auth/start', 'course_id' => $course_id]);
            }
        } else {
            //login
            $model = new LoginForm();

            $model->email = $attributes['email'];
            if ($model->login("google")) {
                return $this->redirect(['dashboard/home']);
            } else {
                foreach ($model->getErrors() as $key => $value) {
                    $error = $value[0];
                }
                Yii::$app->session->setFlash("google-login-error", $error);
                return $this->redirect(['auth/login']);
            }
        }
    }

    public function actionGoogleRedirect($operation) {
        $client = Yii::$app->authClientCollection->getClient('google');
        if ($operation == 'register') {
            if (empty(Yii::$app->request->get("selected"))) return $this->goBack();
            Yii::$app->session->set("selected", json_decode(Yii::$app->request->get("selected"), true));
        }
        return $this->redirect($client->buildAuthUrl());
    }
}