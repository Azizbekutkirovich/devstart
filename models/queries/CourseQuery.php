<?php

namespace app\models\queries;

use yii\db\ActiveQuery;

class CourseQuery extends ActiveQuery
{
    public function withStats()
    {
        return $this->select([
                '{{%courses}}.*',
                'modules_count' => 'COUNT(DISTINCT {{%course_modules}}.module_id)',
                'topics_count' => 'COUNT(DISTINCT {{%topics}}.id)',
                'mentor_name' => '{{%mentors}}.name'
            ])
            ->leftJoin('{{%course_modules}}', '{{%course_modules}}.course_id = {{%courses}}.id')
            ->leftJoin('{{%topics}}', '{{%topics}}.module_id = {{%course_modules}}.module_id')
            ->leftJoin('{{%mentors}}', '{{%mentors}}.id = {{%courses}}.mentor_id')
            ->groupBy('{{%courses}}.id');
    }
}