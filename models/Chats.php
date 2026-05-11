<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "chats".
 *
 * @property int $id
 * @property int $user_data_id
 * @property int|null $topic_id
 * @property int|null $current_stage
 *
 * @property Messages[] $messages
 * @property Topics $topic
 * @property UserData $userData
 */
class Chats extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chats';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topic_id', 'current_stage'], 'default', 'value' => null],
            [['user_data_id'], 'required'],
            [['user_data_id', 'topic_id', 'current_stage', 'total_stages'], 'integer'],
            [['topic_id'], 'exist', 'skipOnError' => true, 'targetClass' => Topics::class, 'targetAttribute' => ['topic_id' => 'id']],
            [['user_data_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserData::class, 'targetAttribute' => ['user_data_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_data_id' => 'User Data ID',
            'topic_id' => 'Topic ID',
            'current_stage' => 'Current Stage',
        ];
    }

    /**
     * Gets query for [[Messages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Messages::class, ['chat_id' => 'id']);
    }

    /**
     * Gets query for [[Topic]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(Topics::class, ['id' => 'topic_id']);
    }

    /**
     * Gets query for [[UserData]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserData()
    {
        return $this->hasOne(UserData::class, ['id' => 'user_data_id']);
    }

    public static function create(int $topic_id, int $current_stage, array $needed_data) {
        $chats = new self();
        $chats->user_data_id = Yii::$app->user->identity->last_active_user_data_id;
        $chats->topic_id = $topic_id;
        $chats->current_stage = $current_stage;
        $chats->total_stages = 0;
        if ($chats->save()) {
            $return_data =  [
                "success" => true,
                "data" => []
            ];

            foreach ($needed_data as $n) {
                if (isset($chats->$n)) {
                    $return_data['data'][$n] = $chats->$n;
                }
            }

            return $return_data;
        } else {
            Yii::error([
                "message" => "Chat hosil qilishda xatolik",
                "user_data" => [
                    "id" => Yii::$app->user->identity->activeData['user_id'],
                ],
                "topic_id" => $topic_id
            ]);
            return [
                "success" => false
            ];
        }
    }
}