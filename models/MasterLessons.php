<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "master_lessons".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $full_content
 * @property int $total_parts
 *
 * @property Chats $chat
 */
class MasterLessons extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master_lessons';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'full_content', 'total_parts'], 'required'],
            [['chat_id', 'total_parts'], 'integer'],
            [['full_content'], 'safe'],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chats::class, 'targetAttribute' => ['chat_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_id' => 'Chat ID',
            'full_content' => 'Full Content',
            'total_parts' => 'Total Parts',
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chats::class, ['id' => 'chat_id']);
    }

}
