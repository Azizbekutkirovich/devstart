<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Users extends ActiveRecord implements \yii\web\IdentityInterface
{
    private $_activeData;

	public static function tableName()
	{
		return "users";
	}

	public function rules() 
	{
		return [
			['fullname', 'string', 'max' => 100, "tooLong" => "Ism va familya 100ta belgidan oshmasligi kerak!"],
			['email', 'unique', 'message' => 'Bu email band!']
		];
	}

	public static function findIdentity($id)
	{
		return self::find()->select(["id", "fullname", "email", "last_active_user_data_id"])->where(['id' => $id])->one();
	}

	public static function findIdentityByAccessToken($token, $type = null)
    {

    }

    /**
     * Finds user by login
     *
     * @param string $login
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return self::find()->where(['email' => $email])->select(["id", "email", "password"])->one();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function getLastActiveUserDataId()
    {
        return $this->last_active_user_data_id; 
    }

    public function getActiveData()
    {
        if ($this->_activeData === null) {
            // Bir marta bazadan olamiz va shu o'zgaruvchiga saqlaymiz
            $this->_activeData = UserData::find()
                ->where(['id' => $this->last_active_user_data_id])
                ->asArray()
                ->one();
        }
        return $this->_activeData;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
    	
    }

    public function setPassword($password)
    {
        $this->password = isset($password) ? Yii::$app->security->generatePasswordHash($password) : null;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        if (!isset($this->password)) return false;
        return Yii::$app->security->validatePassword($password, $this->password);
    }
}