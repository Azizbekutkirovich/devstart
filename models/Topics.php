<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "topics".
 *
 * @property int $id
 * @property int|null $course_id
 * @property string $type
 * @property string|null $title
 * @property string $key_concepts
 *
 * @property Courses $course
 */
class Topics extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'topics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['course_id', 'title'], 'default', 'value' => null],
            [['course_id'], 'integer'],
            [['type', 'key_concepts'], 'required'],
            [['type'], 'string', 'max' => 20],
            [['title'], 'string', 'max' => 100],
            [['key_concepts'], 'string', 'max' => 256],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => Courses::class, 'targetAttribute' => ['course_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'Course ID',
            'type' => 'Type',
            'title' => 'Title',
            'key_concepts' => 'Key Concepts',
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

    public static function getTopicsByCourseId(int $course_id) {
        $topics = self::find()->asArray()->select(['id', 'course_id', 'title'])->where(['course_id' => $course_id])->with(['course.category', 'course.language'])->all();

        $sections = array_chunk($topics, 7);

        $result = [
            "category" => $topics[0]["course"]["category"]["name"] ?? null,
            "language" => $topics[0]["course"]["language"]["name"] ?? null,
            "data" => array_map(function ($sectionTopics, $index) {
                        return [
                            'id' => $index + 1,
                            'name' => ($index + 1) . "-bo'lim",
                            'topics' => array_map(function ($topic) {
                                return [
                                    'id' => $topic['id'],
                                    'title' => $topic['title'],
                                ];
                            }, $sectionTopics)
                        ];
                }, $sections, array_keys($sections))
        ];

        return $result;
    }

    public static function getTopicById(int $id) {
        $data = self::find()->asArray()->where(['id' => $id])->with(['course.category', 'course.language'])->one();

        if (!$data) {
            return null;
        }

        return [
            "id" => $data['id'],
            "type" => $data['type'],
            "title" => $data['title'],
            "key_concepts" => $data['key_concepts'],
            "category" => $data['course']['category']['name'] ?? null,
            "language" => $data['course']['language']['name'] ?? null
        ];
    }
}