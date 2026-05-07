<?php

namespace app\models;

use yii\base\Model;

class SelectedForm extends Model
{
	public $course_id;
	public $level_id;

    public function rules()
    {
        return [
            [['course_id', 'level_id'], 'required'],
            [['course_id', 'level_id'], 'integer'],
            [['course_id'], 'exist',
                'targetClass' => Courses::class,
                'targetAttribute' => 'id',
                'message' => "Bunday kurs mavjud emas!"
            ],
            [['level_id'], 'exist',
            	'targetClass' => Levels::class,
            	'targetAttribute' => 'id',
            	'message' => "Bunday daraja mavjud emas!"
        	]
        ];
    }
}