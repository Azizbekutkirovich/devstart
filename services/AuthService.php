<?php

namespace app\services;

use Yii;
use app\models\Users;

class AuthService
{
	public function autoLoginByEmail(string $email) {
		$user = Users::find()->where(['email' => $email])->one();

		if (!$user) {
			return false;
		}

		if (!Yii::$app->user->login($user)) {
			return false;
		}

		return true;
	}
}