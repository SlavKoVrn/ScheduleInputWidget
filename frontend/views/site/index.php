<?php
use slavko\schedule\ScheduleInputWidget;

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = Yii::t('common','Yii2 Schedule Input Widget');
Yii::$app->view->registerMetaTag([
    'name' => 'keywords',
    'content' => Yii::t('common','Yii2 Schedule Input Widget')
]);
Yii::$app->view->registerMetaTag([
    'name' => 'description',
    'content' => Yii::t('common','Yii2 Schedule Input Widget')
]);
?>
<div class="contact container">
    <div class="row info">

        <div class="col-lg-12">

            <section id="blog-details" class="blog-details section">
                <div class="container">

<pre>
class ScheduleForm extends \yii\base\Model
{
    public $schedule;
    public $enable_time_zone;
    public $enable_production_calendar;

    public function rules()
    {
        return [
            [['schedule'], 'required'],
            [['schedule','enable_time_zone','enable_production_calendar'], 'safe'],
            ['schedule', function ($attribute, $params) {
                ScheduleValidator::validateSchedule($attribute, $params, $this);
            }],
        ];
    }
    public function behaviors()
    {
        return [
            'beforeValidate' => [
                'class' => ScheduleBehavior::class,
                'attribute' => 'schedule',
            ],
        ];
    }
}
$model = new ScheduleForm;
$form = ActiveForm::begin();
$form->field($model, 'schedule')->widget(ScheduleInputWidget::class);
</pre>

                    <?php $form = ActiveForm::begin([
                        'id' => 'validate-multiple-form',
                        'action' => '/site/index',
                        'validationUrl' => '/site/validate',
                        'options' => [
                            'class' => 'php-email-form',
                        ],
                    ]); ?>

                    <?= $form->field($model, 'schedule')->widget(ScheduleInputWidget::class,[
                        'formId' => $form->id,
                        'removeButtonOptions' => [ 'class' =>'btn btn-success'],
                    ])->label(Html::tag('h1',Yii::t('common','Yii2 Schedule Input Widget'),[
                        'style' => 'font-size:22px'
                    ])); ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('common', 'Output'), ['class' => 'btn btn-success']) ?>
                    </div>

                    <pre id="code"></pre>

                    <?php ActiveForm::end(); ?>

                    <?php $form2 = ActiveForm::begin([
                        'id' => 'validate-multiple-form-2',
                        'action' => '/site/index',
                        'validationUrl' => '/site/validate',
                        'options' => [
                            'class' => 'php-email-form',
                        ],
                    ]); ?>

                    <?= $form2->field($model2, 'schedule')->widget(ScheduleInputWidget::class,[
                        'formId' => $form2->id,
                        'removeButtonOptions' => [ 'class' =>'btn btn-success'],
                    ])->label(Html::tag('h1',Yii::t('common','Yii2 Schedule Input Widget'),[
                        'style' => 'font-size:22px'
                    ])); ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('common', 'Output'), ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </section>

        </div>
    </div>
</div>
<style>
    pre {
        display: block;
        padding: 9.5px;
        margin: 0 0 10px;
        font-size: 13px;
        line-height: 1.42857143;
        color: #333333;
        word-break: break-all;
        word-wrap: break-word;
        background-color: #f5f5f5;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
</style>
