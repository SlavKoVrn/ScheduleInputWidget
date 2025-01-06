<?php
namespace slavko\schedule;

use yii\web\AssetBundle;

class ScheduleInputWidgetAsset extends AssetBundle
{
    public $sourcePath = '@slavko/schedule/assets/src';
    public $css = [
        'css/daterangepicker.css',
        'css/jquery.timepicker.css',
        'css/bootstrap-datetimepicker4.min.css',
	'css/bootstrap3-glyphicons.min.css',
    ];
    public $js = [
        'js/moment.min.js',
        'js/daterangepicker.js',
        'js/jquery.timepicker.js',
        'js/bootstrap-datetimepicker.js',
        'js/locales/bootstrap-datetimepicker.ru.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}