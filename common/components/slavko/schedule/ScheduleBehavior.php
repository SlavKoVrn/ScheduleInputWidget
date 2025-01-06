<?php
namespace slavko\schedule;

use yii\base\Model;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;

class ScheduleBehavior extends Behavior
{
    /**
     * @var Model the owner model of this behavior.
     */
    public $owner;

    /**
     * @var string the attribute that containing date range value.
     */
    public $attribute = 'schedule';

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!isset($this->attribute)) {
            throw new InvalidConfigException('The "attribute" property must be specified.');
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Model::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    /**
     * Handles owner 'beforeValidate' event.
     *
     * @param \yii\base\Event $event event instance.
     * @throws InvalidValueException
     */
    public function beforeValidate($event)
    {
        $schedule = $this->owner->{$this->attribute};

        if (empty($schedule)) {
            return;
        }
        $data = [
            'schedule' => [
                'enable_time_zone' => $this->owner->enable_time_zone,
                'enable_production_calendar' => $this->owner->enable_production_calendar,
                'work_time' => [],
                'special_time' => [],
            ]
        ];
        foreach ($schedule as $key => $day){
            $ex = explode('#',$day['start_end']);
            $status = $day[ScheduleInputWidget::STATUS];
            if ($status == '1'){
                $data['schedule']['work_time'][$key] = [
                    'day' => $day[ScheduleInputWidget::WEEKDAY],
                    'start_time' => $ex[0],
                    'end_time' => $ex[1],
                ];
            }else{
                $data['schedule']['special_time'][$key] = [
                    'start_time' => $ex[0],
                    'end_time' => $ex[1],
                ];
            }
        }
        $this->owner->{$this->attribute} = $data;
    }

}
