<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "topics".
 *
 * @property int $id
 * @property int $module_id
 * @property string|null $type
 * @property string $title
 * @property string|null $key_concepts
 * @property int|null $order_weight
 *
 * @property Chats[] $chats
 * @property Modules $module
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
            [['type', 'key_concepts'], 'default', 'value' => null],
            [['order_weight'], 'default', 'value' => 0],
            [['module_id', 'title'], 'required'],
            [['module_id', 'order_weight'], 'integer'],
            [['key_concepts'], 'safe'],
            [['type'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 100],
            [['module_id'], 'exist', 'skipOnError' => true, 'targetClass' => Modules::class, 'targetAttribute' => ['module_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module_id' => 'Module ID',
            'type' => 'Type',
            'title' => 'Title',
            'key_concepts' => 'Key Concepts',
            'order_weight' => 'Order Weight',
        ];
    }

    /**
     * Gets query for [[Chats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChats()
    {
        return $this->hasMany(Chats::class, ['topic_id' => 'id']);
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
