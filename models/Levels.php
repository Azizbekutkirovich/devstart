<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "levels".
 *
 * @property int $id
 * @property string $name
 *
 * @property UserData[] $userDatas
 */
class Levels extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'levels';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description', 'icon'], 'required'],
            [['description'], 'string'],
            [['title', 'icon'], 'string', 'max' => 256],
        ];
    }

    /**
     * Gets query for [[UserDatas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserDatas()
    {
        return $this->hasMany(UserData::class, ['level_id' => 'id']);
    }

    public static function getAll() {
        return self::find()
            ->asArray()
            ->all();
    }

    public static function getById($id) {
        return self::find()->asArray()->select(['name'])->where(['id' => $id])->one();
    }
}