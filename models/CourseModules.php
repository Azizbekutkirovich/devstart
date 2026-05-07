<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "course_modules".
 *
 * @property int $course_id
 * @property int $module_id
 * @property int|null $section_order
 *
 * @property Courses $course
 * @property Modules $module
 */
class CourseModules extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'course_modules';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['section_order'], 'default', 'value' => 0],
            [['course_id', 'module_id'], 'required'],
            [['course_id', 'module_id', 'section_order'], 'integer'],
            [['course_id', 'module_id'], 'unique', 'targetAttribute' => ['course_id', 'module_id']],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => Courses::class, 'targetAttribute' => ['course_id' => 'id']],
            [['module_id'], 'exist', 'skipOnError' => true, 'targetClass' => Modules::class, 'targetAttribute' => ['module_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'course_id' => 'Course ID',
            'module_id' => 'Module ID',
            'section_order' => 'Section Order',
        ];
    }

    /**
     * Gets query for [[Course]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Courses::class, ['id' => 'course_id']);
    }

    /**
     * Gets query for [[Module]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModule()
    {
        return $this->hasOne(Modules::class, ['id' => 'module_id']);
    }

}
