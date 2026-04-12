<?php

namespace app\models;

use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
	public $fullname;
	public $email;
	public $password;

	public function rules()
	{
		return [
			[['fullname', 'email', 'password'], 'required', "message" => "Maydonni to'ldirish shart!"],
			['fullname', 'string', 'max' => 100, "tooLong" => "Ism va familya 100ta belgidan oshmasligi kerak!"],
			['email', 'email', "message" => "Email manzili to'g'ri emas!"],
			['email', 'unique', 'targetClass' => '\app\models\Users', 'message' => 'Bu email band!'],
			['password', 'string', 'min' => 8, 'tooShort' => "Parol kamida 8ta belgidan iborat bo'lishi kerak!"],
			['password', 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+$/', 'message' => 'Parolda katta harf, kichik harf, va raqam bo‘lishi shart!']
		];
	}

	public function scenarios()
	{
		$scenarios = parent::scenarios();
	    $scenarios['scenarioWithGoogle'] = ['fullname', 'email'];
	    return $scenarios;
	}

	public function register($type, $selected_model, $google_id = null) {
		if (!$selected_model->validate()) {
			return false;
		}

		if ($type == 'google') {
			$this->scenario = 'scenarioWithGoogle';
		}

		if (!$this->validate()) {
			return false;
		}

		$email_verified = $type == "google" ? 1 : 0;

		return $this->createUser($google_id, $email_verified, $selected_model->course_id, $selected_model->level_id);
	}

	private function createUser($google_id, $email_verified, $course_id, $level_id) {
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$user = new Users();
			$user->fullname = $this->fullname;
			$user->email = $this->email;
			$user->setPassword($this->password);
			$user->google_id = $google_id;
			$user->email_verified = $email_verified;
			
			if (!$user->save(false)) {
				throw new \Exception("CREATE_USER_FAILED");
			}

			if (!$this->saveUserData($user->id, $course_id, $level_id)) {
				throw new \Exception("SAVE_USER_DATA_FAILED");
			}

			$transaction->commit();
			return true;
		} catch (\Throwable $e) {
			$transaction->rollback();
			
			Yii::error([
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
				'user_data' => [
					'email' => $this->email,
					'fullname' => $this->fullname
				]
			], __METHOD__);

		    $this->addError('system', "Tizimda xatolik yuz berdi! Iltimos keyinroq urinib ko'ring");

			return false;
		}		
	}

	private function saveUserData($user_id, $course_id, $level_id) {
		$user_data = new UserData();
		$user_data->user_id = $user_id;
		$user_data->course_id = $course_id;
		$user_data->level_id = $level_id;
		return $user_data->save(false);
	}
}