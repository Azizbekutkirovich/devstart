<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "categories".
 *
 * @property int $id
 * @property string $name
 * @property string $title
 *
 * @property Courses[] $courses
 * @property UserData[] $userDatas
 */
class Categories extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'title'], 'required'],
            [['name'], 'string', 'max' => 256],
            [['title'], 'string', 'max' => 512],
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
        ];
    }

    /**
     * Gets query for [[Courses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourses()
    {
        return $this->hasMany(Courses::class, ['category_id' => 'id']);
    }

    public static function getStructuredCategories()
    {
        $categories = self::find()
            ->with(['courses.language'])
            ->asArray()
            ->all();

        $result = [];

        foreach ($categories as $category) {

            $languages = [];

            if (!empty($category['courses'])) {
                foreach ($category['courses'] as $course) {

                    if (!empty($course['language'])) {
                        $languages[] = [
                            'language_id' => $course['language']['id'],
                            'language'  => $course['language']['name'],
                            'title'     => $course['language_title'],
                            'course_id' => $course['id'],
                        ];
                    }
                }
            }

            $result[] = [
                'category_id' => $category['id'],
                'category'  => $category['name'],
                'title'     => $category['title'],
                'languages' => $languages,
            ];
        }

        return $result;
    }
}