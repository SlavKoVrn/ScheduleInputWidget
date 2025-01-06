<?php

namespace slavko\schedule;

use Yii;
use yii\base\Model;

class ScheduleForm extends Model
{

    public $schedule;
    public $enable_time_zone;
    public $enable_production_calendar;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['schedule'], 'required'],
            [['schedule','enable_time_zone','enable_production_calendar'], 'safe'],
            ['schedule', 'validateSchedule'],
            ['schedule', function ($attribute, $params) {
                ScheduleValidator::validateSchedule($attribute, $params, $this);
            }],
        ];
    }

    public function behaviors()
    {
        return [
            'beforeValidate' => [
                'class' => \slavko\schedule\ScheduleBehavior::class,
                'attribute' => 'schedule',
            ],
        ];
    }

    public function init()
    {
        parent::init();

        $cache = Yii::$app->cache;
        if ($cache->exists('schedule')) {
            $this->schedule = $cache->get('schedule');
        }else{
            $this->schedule = [
                'schedule' => [
                    'enable_time_zone' => '1',
                    'enable_production_calendar' => '1',
                    'work_time' => [
                        0 => [
                            'day' => '1',
                            'start_time' => '08:00',
                            'end_time' => '18:00',
                        ],
                        1 => [
                            'day' => '2',
                            'start_time' => '08:00',
                            'end_time' => '18:00',
                        ],
                        2 => [
                            'day' => '3',
                            'start_time' => '08:00',
                            'end_time' => '18:00',
                        ],
                        3 => [
                            'day' => '4',
                            'start_time' => '08:00',
                            'end_time' => '18:00',
                        ],
                        4 => [
                            'day' => '5',
                            'start_time' => '08:00',
                            'end_time' => '18:00',
                        ],
                    ],
                    'special_time' => [
                        0 => [
                            'start_time' => '2025-01-01 08:00:00',
                            'end_time' => '2025-01-12 18:00:00',
                        ],
                        1 => [
                            'start_time' => '2025-01-20 02:20:00',
                            'end_time' => '2025-01-26 22:20:00',
                        ],
                    ],
                ],
            ];
        }
    }

    public function validateSchedule($attribute, $params)
    {
        //$this->addError($attribute, Yii::t('common', 'You entered an invalid date format.'));
        $cache = Yii::$app->cache;
        $cache->set('schedule', $this->schedule, 24 * 3600);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'schedule' => Yii::t('common','Schedule'),
            'enable_time_zone' => Yii::t('common','Use time zone'),
            'enable_production_calendar' => Yii::t('common','Production calendar'),
        ];
    }

}
