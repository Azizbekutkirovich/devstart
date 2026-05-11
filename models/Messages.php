<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "messages".
 *
 * @property int $id
 * @property int|null $chat_id
 * @property string $sender_role
 * @property string $type
 * @property string $content
 * @property string $created_at
 *
 * @property Chats $chat
 */
class Messages extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const SENDER_ROLE_USER = 'user';
    const SENDER_ROLE_MENTOR = 'mentor';
    const SENDER_ROLE_SYSTEM = 'system';
    const SENDER_ROLE = '';
    const TYPE_TEXT = 'text';
    const TYPE_QUIZ = 'quiz';
    const TYPE_COMMAND = 'command';
    const TYPE_PRACTICE = 'practice';
    const TYPE_TOPIC = 'topic';
    const TYPE_PRACTICE_RESULT = 'practice_result';
    const TYPE = '';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'messages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'step_topic_index'], 'default', 'value' => null],
            [['chat_id', 'step_topic_index'], 'integer'],
            [['sender_role', 'type', 'content'], 'required'],
            [['sender_role', 'type'], 'string'],
            [['content', 'created_at'], 'safe'],
            ['sender_role', 'in', 'range' => array_keys(self::optsSenderRole())],
            ['type', 'in', 'range' => array_keys(self::optsType())],
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
            'sender_role' => 'Sender Role',
            'type' => 'Type',
            'content' => 'Content',
            'created_at' => 'Created At',
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


    /**
     * column sender_role ENUM value labels
     * @return string[]
     */
    public static function optsSenderRole()
    {
        return [
            self::SENDER_ROLE_USER => 'user',
            self::SENDER_ROLE_MENTOR => 'mentor',
            self::SENDER_ROLE_SYSTEM => 'system',
            self::SENDER_ROLE => '',
        ];
    }

    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
    {
        return [
            self::TYPE_TEXT => 'text',
            self::TYPE_QUIZ => 'quiz',
            self::TYPE_COMMAND => 'command',
            self::TYPE_PRACTICE => 'practice',
            self::TYPE_TOPIC => 'topic',
            self::TYPE_PRACTICE_RESULT => 'practice_result',
            self::TYPE => '',
        ];
    }

    /**
     * @return string
     */
    public function displaySenderRole()
    {
        return self::optsSenderRole()[$this->sender_role];
    }

    /**
     * @return bool
     */
    public function isSenderRoleUser()
    {
        return $this->sender_role === self::SENDER_ROLE_USER;
    }

    public function setSenderRoleToUser()
    {
        $this->sender_role = self::SENDER_ROLE_USER;
    }

    /**
     * @return bool
     */
    public function isSenderRoleMentor()
    {
        return $this->sender_role === self::SENDER_ROLE_MENTOR;
    }

    public function setSenderRoleToMentor()
    {
        $this->sender_role = self::SENDER_ROLE_MENTOR;
    }

    /**
     * @return bool
     */
    public function isSenderRoleSystem()
    {
        return $this->sender_role === self::SENDER_ROLE_SYSTEM;
    }

    public function setSenderRoleToSystem()
    {
        $this->sender_role = self::SENDER_ROLE_SYSTEM;
    }

    /**
     * @return bool
     */
    public function isSenderRole()
    {
        return $this->sender_role === self::SENDER_ROLE;
    }

    public function setSenderRoleTo()
    {
        $this->sender_role = self::SENDER_ROLE;
    }

    /**
     * @return string
     */
    public function displayType()
    {
        return self::optsType()[$this->type];
    }

    /**
     * @return bool
     */
    public function isTypeText()
    {
        return $this->type === self::TYPE_TEXT;
    }

    public function setTypeToText()
    {
        $this->type = self::TYPE_TEXT;
    }

    /**
     * @return bool
     */
    public function isTypeQuiz()
    {
        return $this->type === self::TYPE_QUIZ;
    }

    public function setTypeToQuiz()
    {
        $this->type = self::TYPE_QUIZ;
    }

    /**
     * @return bool
     */
    public function isTypeCommand()
    {
        return $this->type === self::TYPE_COMMAND;
    }

    public function setTypeToCommand()
    {
        $this->type = self::TYPE_COMMAND;
    }

    /**
     * @return bool
     */
    public function isType()
    {
        return $this->type === self::TYPE;
    }

    public function setTypeTo()
    {
        $this->type = self::TYPE;
    }

    public static function create($chat_id, $sender_role, $type, $content, $step_topic_index = null)
    {
        $model = new self();
        $model->chat_id = $chat_id;
        $model->sender_role = $sender_role;
        $model->type = $type;
        $model->content = $content;
        $model->step_topic_index = $step_topic_index;

        if ($model->save()) {
            return true;
        } else {
            Yii::error([
                "message" => "Chat message hosil qilishda xatolik",
                "user_data" => [
                    "id" => Yii::$app->user->identity->activeData['user_id'],
                ],
                "chat_id" => $chat_id
            ]);
            return false;
        }
    }
}