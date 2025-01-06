<?php
namespace slavko\schedule;

use Yii;

class ScheduleValidator
{
    public static function validateSchedule($attribute, $params, $model) {


        $weekdays = ScheduleInputWidget::weekDays();

        $schedule = $model->$attribute;

        $workTime = $schedule['schedule']['work_time'];

        $daysMet = [];
        foreach ($workTime as $key => $day) {
            $startTime = strtotime($day['start_time']);
            $endTime = strtotime($day['end_time']);

            if ($startTime >= $endTime) {
                $model->addError($attribute, $key.'. ' . $weekdays[$day['day']] . ' ' . Yii::t('common','Start time {start_time} must be before end time {end_time}',[
                    'start_time' => $day['start_time'],
                    'end_time' => $day['end_time'],
                ]));
            }

            if (!in_array($day['day'],$daysMet)){
                $daysMet[] = $day['day'];
            } else {
                $model->addError($attribute, $key.'. ' . $weekdays[$day['day']] . ' ' . Yii::t('common','Start time {start_time} end time {end_time} worktime week day already exists',[
                        'start_time' => $day['start_time'],
                        'end_time' => $day['end_time'],
                    ]));
            }
        }

        $specialTime = $schedule['schedule']['special_time'];

        foreach ($specialTime as $key => $day) {
            $startTime = strtotime($day['start_time']);
            $endTime = strtotime($day['end_time']);

            if ($startTime >= $endTime) {
                $model->addError($attribute, $key.'. ' . $weekdays[date('w',$startTime)] . ' ' . Yii::t('common','Start time {start_time} must be before end time {end_time}',[
                        'start_time' => date('d.m.Y H:i',strtotime($day['start_time'])),
                        'end_time' => date('d.m.Y H:i',strtotime($day['end_time'])),
                    ]));
            }
        }

        $currentYear = date('Y');

        foreach ($specialTime as $key => $period) {
            $startTime = strtotime($period['start_time']);
            $endTime = strtotime($period['end_time']);

            // Check if start and end times are within the current year
            if (date('Y', $startTime) !== $currentYear || date('Y', $endTime) !== $currentYear) {
                $model->addError($attribute, $key.'. ' . $weekdays[date('w',$startTime)] . ' ' . Yii::t('common','Start time {start_time} or end time {end_time} not within the current year.',[
                        'start_time' => date('d.m.Y H:i',strtotime($period['start_time'])),
                        'end_time' => date('d.m.Y H:i',strtotime($period['end_time'])),
                    ]));
            }
        }

        // Sort periods by start time
        uasort($specialTime, function ($a, $b) {
            return strtotime($a['start_time']) - strtotime($b['start_time']);
        });

        $lastOverlappingPeriodKey = null;
        foreach ($specialTime as $key => $period) {
            //Skip first period, it cannot overlap
            if ($key === key($specialTime)) continue;

            $startTime = strtotime($period['start_time']);
            $endTime = strtotime($period['end_time']);

            foreach ($specialTime as $prevKey => $prevPeriod){
                if ($key === $prevKey) continue;
                if ($prevKey < $key) {
                    $prevStartTime = strtotime($prevPeriod['start_time']);
                    $prevEndTime = strtotime($prevPeriod['end_time']);

                    // Check for overlap
                    if ($startTime < $prevEndTime && $endTime > $prevStartTime) {
                        $lastOverlappingPeriodKey = $key;
                        break; //Found overlap, no need to check further
                    }
                }
            }
        }

        if ($lastOverlappingPeriodKey !== null) {
            $model->addError($attribute, Yii::t('common','Special periods overlap. Please adjust the timeframes.'));
        }

    }
}
