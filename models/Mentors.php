<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mentors".
 *
 * @property int $id
 * @property string|null $name
 * @property string $titile
 * @property string|null $description
 * @property string $skills
 * @property string|null $landing_img
 * @property string $chat_img
 * @property string $personality
 *
 * @property Courses[] $courses
 */
class Mentors extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mentors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'landing_img'], 'default', 'value' => null],
            [['title', 'skills', 'chat_img', 'personality'], 'required'],
            [['description', 'skills'], 'string'],
            [['name', 'titile', 'landing_img', 'chat_img', 'personality'], 'string', 'max' => 256],
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
            'title' => 'Title',
            'description' => 'Description',
            'skills' => 'Skills',
            'landing_img' => 'Landing Img',
            'chat_img' => 'Chat Img',
            'personality' => 'Personality',
        ];
    }

    /**
     * Gets query for [[Courses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourses()
    {
        return $this->hasMany(Courses::class, ['mentor_id' => 'id']);
    }

    public static function getAll() {
        return self::find()->select(['name', 'title', 'description', 'skills', 'landing_img'])->asArray()->all();
    }
}
