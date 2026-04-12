<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Categories;
use app\models\Levels;
use app\models\RegisterForm;
use app\models\SelectedForm;
use app\models\LoginForm;
use app\services\AuthService;

class AuthController extends Controller
{
	public function behaviors() {
		return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['start', 'login', 'register', 'google-callback', 'google-redirect'],
                'rules' => [
                    [
                        'actions' => ['start', 'login', 'register', 'google-callback', 'google-redirect'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ]
        ];
	}

    public function actionTest() {
        Yii::$app->user->logout();
        return $this->goBack();
    }

	public function actionStart() {
		$this->layout = "auth";
        $registerModel = new RegisterForm();
        $data = Categories::getStructuredCategories();
        $levels = Levels::getAllLevels();
		return $this->render("start", [
            "registerModel" => $registerModel,
            "data" => $data,
            "levels" => $levels
        ]);
	}

    public function actionLogin() {
        $this->layout = 'auth';
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login('form')) {
            return $this->redirect(['site/home']);
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
            if ($selected_model->load($selected, '') && $model->register("google", $selected_model, $attributes['id'])) {
                Yii::$app->session->remove("selected");
                $auth = new AuthService();
                if ($auth->autoLoginByEmail($model->email)) {
                    return $this->redirect(['site/home']);
                } else {
                    $error = "Siz muvaffaqiyatli roʻyxatdan oʻtdingiz, ammo akkauntga kirishda muammo yuz berdi. Iltimos, <a href='/devstart/auth/login'>Kirish</a> havolasi orqali akkauntingizga kiring";
                    Yii::$app->session->setFlash("google-register-errors", ["login" => ["$error"]]);
                    return $this->redirect(['auth/start']);
                }
            } else {
                Yii::$app->session->remove("selected");
                Yii::$app->session->setFlash("google-register-errors", array_merge_recursive($selected_model->getErrors(), $model->getErrors()));
                return $this->redirect(['auth/start']);
            }
        } else {
            //login
            $model = new LoginForm();

            $model->email = $attributes['email'];
            if ($model->login("google")) {
                return $this->redirect(['site/home']);
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
            $selected = json_decode(Yii::$app->request->get("selected"), true);
            Yii::$app->session->set("selected", $selected);
        }
        return $this->redirect($client->buildAuthUrl());
    }
}