<?php

namespace app\models;

use Yii;
use app\models\queries\CourseQuery;

/**
 * This is the model class for table "courses".
 *
 * @property int $id
 * @property int $mentor_id
 * @property string $name
 * @property string $title
 * @property string $img
 *
 * @property CourseModules[] $courseModules
 * @property Mentors $mentor
 * @property Modules[] $modules
 * @property UserData[] $userDatas
 */
class Courses extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'courses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mentor_id', 'name', 'title', 'img'], 'required'],
            [['mentor_id'], 'integer'],
            [['title'], 'string'],
            [['name', 'img'], 'string', 'max' => 256],
            [['mentor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mentors::class, 'targetAttribute' => ['mentor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mentor_id' => 'Mentor ID',
            'name' => 'Name',
            'title' => 'Title',
            'img' => 'Img',
        ];
    }

    /**
     * Gets query for [[CourseModules]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourseModules()
    {
        return $this->hasMany(CourseModules::class, ['course_id' => 'id']);
    }

    /**
     * Gets query for [[Mentor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMentor()
    {
        return $this->hasOne(Mentors::class, ['id' => 'mentor_id']);
    }

    /**
     * Gets query for [[Modules]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModules()
    {
        return $this->hasMany(Modules::class, ['id' => 'module_id'])->viaTable('course_modules', ['course_id' => 'id']);
    }

    public function getTopics()
    {
        return $this->hasMany(Topics::class, ['module_id' => 'id'])
                    ->via('modules');
    }

    /**
     * Gets query for [[UserDatas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserDatas()
    {
        return $this->hasMany(UserData::class, ['course_id' => 'id']);
    }

    public static function find()
    {
        return new CourseQuery(get_called_class());
    }
}