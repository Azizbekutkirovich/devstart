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
            [['name'], 'required'],
            [['name'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
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

    public static function getAllLevels() {
        return self::find()
            ->asArray()
            ->all();
    }

    public static function getLevelById($id) {
        return self::find()->asArray()->select(['name'])->where(['id' => $id])->one();
    }
}