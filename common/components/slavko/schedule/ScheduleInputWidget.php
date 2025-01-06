<?php
namespace slavko\schedule;

use Yii;
use yii\helpers\Html;

class ScheduleInputWidget extends MultipleInput
{
    const STATUS    = 'status';
    const START     = 'start_time';
    const END       = 'end_time';
    const WEEKDAY   = 'weekday';
    const DAY       = 'day';

    private $status     = self::STATUS;
    private $start      = self::START;
    private $end        = self::END;
    private $weekday    = self::WEEKDAY;
    private $day        = self::DAY;

    /**
     * @var string
     */
    public $errorRowMarkColor = 'green';

    /**
     * @var string
     */
    public $formId = '';

    /**
     * @var string
     */
    public static $autoIdPrefix = 'ScheduleInputWidget';

    /**
     * @var array опции контейнера
     */
    public $options = [];
    /**
     * @var array
     */
    public $clientOptions = [];

    private $enableTimeZone = null;
    private $enableProductionCalendar = null;

    private function dayOfWeek($timestamp)
    {
        $dayOfWeek = date("w", $timestamp);
        if ($dayOfWeek == 0){
            return 7;
        }
        return $dayOfWeek;
    }

    public static function weekDays()
    {
        return [
            1 => Yii::t('common', 'Monday'),
            2 => Yii::t('common', 'Tuesday'),
            3 => Yii::t('common', 'Wednesday'),
            4 => Yii::t('common', 'Thursday'),
            5 => Yii::t('common', 'Friday'),
            6 => Yii::t('common', 'Saturday'),
            7 => Yii::t('common', 'Sunday'),
        ];
    }
    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->options['id'] = $this->id."-widget";
        $this->options['class'] = "";
        $this->clientOptions['id'] = $this->id."-widget";

        $data = $this->model->schedule;
        $_data = [];
        if (isset($data['schedule']['work_time']) and count($data['schedule']['work_time'])){
            foreach ($data['schedule']['work_time'] as $day){
                $_data[] = [
                    'status' => 1,
                    'weekday' => $day['day'],
                    'start_time' => $day['start_time'],
                    'end_time' => $day['end_time'],
                ];
            }
        }
        if (isset($data['schedule']['special_time']) and count($data['schedule']['special_time'])){
            foreach ($data['schedule']['special_time'] as $day){
                $_data[] = [
                    'status' => 2,
                    'weekday' => $this->dayOfWeek(strtotime($day['start_time'])),
                    'start_time' => date('d.m.Y H:i',strtotime($day['start_time'])),
                    'end_time' => date('d.m.Y H:i',strtotime($day['end_time'])),
                ];
            }
        }

        if (isset($data['schedule']['enable_time_zone'])){
            $this->enableTimeZone = $data['schedule']['enable_time_zone'];
        }

        if (isset($data['schedule']['enable_production_calendar'])){
            $this->enableProductionCalendar = $data['schedule']['enable_production_calendar'];
        }

        $this->data = $_data;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        ScheduleInputWidgetAsset::register($this->view);
        $this->registerJS();

        $this->id = 'multiple-input';
        $this->min = 0;
        //$this->max = 7;
        $this->allowEmptyList = false;
        $this->enableGuessTitle = true;
        $this->addButtonPosition = MultipleInput::POS_HEADER;
        $this->sortable = true;
        // $this->cloneButton = true;
        $this->columns = [
            [
                'name'  => $this->status,
                'type'  => 'dropDownList',
                'title' => Yii::t('common', 'Status'),
                'items' => [
                    1 => Yii::t('common', 'Working time'),
                    2 => Yii::t('common', 'Special days'),
                ],
            ],
            [
                'name' => 'list_date_range',
                'title' => '<i class="fas fa-calendar-alt"></i>',
                'options' => [
                    'type' => 'hidden',
                    'id' => 'list_date_range',
                ],
            ],
            [
                'name'  => $this->weekday,
                'type'  => 'dropDownList',
                'title' => Yii::t('common', 'Day of week'),
                'items' => self::weekDays(),
            ],
            [
                'name' => $this->start,
                'type' => 'textInput',
                'title' => Yii::t('common', 'Begin time'),
                'options' => [
                    'class' => 'picker',
                ],
            ],
            [
                'name'  => $this->end,
                'type' => 'textInput',
                'title' => Yii::t('common', 'End time'),
                'options' => [
                    'class' => 'picker',
                ],
            ],
            [
                'name' => 'start_end',
                'title' => '',
                'options' => [
                    'type' => 'hidden',
                    'id' => 'start_end',
                ],
            ],
        ];


        $checkboxTimeZone = CheckboxImageWidget::widget([
            'label' => Yii::t('common','Use time zone'),
            'id' => 'enable_time_zone',
            'name' => Html::getInputName($this->model, 'enable_time_zone'),
            'value' => $this->enableTimeZone,
        ]);
        $checkboxProductionCalendar = CheckboxImageWidget::widget([
            'label' => Yii::t('common','Production calendar'),
            'id' => 'enable_production_calendar',
            'name' => Html::getInputName($this->model, 'enable_production_calendar'),
            'value' => $this->enableProductionCalendar,
        ]);
        return  $checkboxTimeZone . $checkboxProductionCalendar . parent::run();
    }

    private function registerJS()
    {
        $formId = $this->formId;

        $js =<<<JS

        $("#$formId").on('beforeSubmit', function () {
            if ($(this).data("yiiActiveForm").validated == false ) {
                return false;
            }
            $('table.multiple-input-list tbody tr').each(function() {
                let start = '';
                let end = '';
                let status = $(this).find('.list-cell__{$this->status} select').val();
                if (status === '1') {
                    start = $(this).find('.list-cell__{$this->start} input').val();
                    end =   $(this).find('.list-cell__{$this->end} input').val();
                } else {
                    start = window.convertDateFormat($(this).find('.list-cell__{$this->start} input').val());
                    end =   window.convertDateFormat($(this).find('.list-cell__{$this->end} input').val());
                }
                let range = start + '#' + end;
                $(this).find('.list-cell__start_end input').val(range);
            });
            $.ajax({
                type: 'POST',
                url: $(this).data('yiiActiveForm').settings.validationUrl,
                data: $(this).serialize(),
                dataType: 'html',
                success: function(data){
                    $('#code').html(data);
                    var errorRows = window.getErrorRows(data);
                    window.addErrorStyle(errorRows);
                }
            });
            return false;
        });

        window.getErrorRows = function(text) {
            if (!text.includes('Ошибки')) {
                return [];
            }
            const errorSection = text.substring(text.indexOf('(') + 1, text.lastIndexOf(')'));
            const errorLines = errorSection.split(`\n`);
            const errorRows = [];
            errorLines.forEach(line => {
                const match = line.match(/=> (\d+)\./);
                if (match) {
                    errorRows.push(parseInt(match[1], 10));
                }
            });
            return errorRows;
        }
        
        window.addErrorStyle = function(errorIndices) {
            const rows = document.querySelectorAll('tr');
            rows.forEach(row => {
                row.setAttribute('style', ``); 
            })
            errorIndices.forEach(index => {
                const row = Array.from(rows).find(row => row.dataset.index === index.toString());
                if (row) {
                    row.setAttribute('style', `border: 2px solid $this->errorRowMarkColor;`);
                }
            });
        }

        $.fn.datetimepicker.DPGlobal.formatDate = function (date, format, language, type, timezone) {
          const options = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            timeZone: 'Europe/London',
          };
          const formatter = new Intl.DateTimeFormat(language, options);
          let formatted = formatter.format(date);
          return formatted.replace(/,/g, '');
        };
        $.fn.datetimepicker.DPGlobal.parseFormat = function (format, type) {
            format = 'dd.mm.yyyy hh:ii';
            var separators = format.replace(this.validParts(type), '\0').split('\0'),
                parts = format.match(this.validParts(type));
            if (!separators || !separators.length || !parts || parts.length === 0) {
                throw new Error('Invalid date format.');
            }
            return {separators: separators, parts: parts};
        };

        window.convertDateFormat = function(originalDate){
            let momentDate = moment(originalDate, "DD.MM.YYYY HH:mm");
            return momentDate.format("YYYY-MM-DD HH:mm:ss");
        };

        $('#multiple-input').on('afterInit', function(){
            var rows = $('table.multiple-input-list tr');
            rows.each((index, element) => {
                var row = $(element);
                var selectedStatus = row.find('td.list-cell__{$this->status}').find('select').val();
                if (selectedStatus === '2') {
                    row.find('.picker').each((index, element) => {
                        let picker = $(element);
                        let value = $(element).val();
                        let newPicker = $('<input type="text" class="picker form-control" value="' + value + '">');
                        picker.replaceWith(newPicker);
                        if (index == 0){
                            newPicker.datetimepicker({language:'ru',autoclose:true}).on('hide',function(e){
                                let weekDay = moment(e.date).day();
                                let converted = (weekDay == 0) ? 6 : weekDay - 1;
                                row.find('td.list-cell__{$this->weekday}').find('select').prop('selectedIndex', converted);
                            });
                        }else{
                            newPicker.datetimepicker({language:'ru',autoclose:true});
                        }
                    });
    
                    let dateRangeList = row.find('#list_date_range');
                    let dateRangePickerId = 'date_range_'+ Date.now();
                    let dateRangePlugin = $('<i id="' + dateRangePickerId + '" class="fas fa-calendar-alt list_date_range" style="cursor:pointer"></i>');
                    dateRangeList.replaceWith(dateRangePlugin);
                    dateRangePlugin.daterangepicker({
                        startDate: moment(row.find('td.list-cell__{$this->start} input').val(),'DD.MM.YYYY HH:mm'),
                        endDate: moment(row.find('td.list-cell__{$this->end} input').val(),'DD.MM.YYYY HH:mm'),
                        format: 'DD.MM.YYYY',
                        showDropdowns: true,
                        timePicker: true,
                        timePicker24Hour:true,
                        ranges: {
                           'Сегодня': [moment().subtract(1, 'days'), moment()],
                           'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                           '6 дней': [moment().subtract(6, 'days'), moment()],
                           '30 дней': [moment().subtract(29, 'days'), moment()],
                           'Этот месяц': [moment().startOf('month'), moment().endOf('month')],
                           'Предыдущий месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                        },
                        locale: {
                            format: 'DD.MM.YYYY',
                            separator: ' - ',
                            applyLabel: "Сохранить",
                            cancelLabel: "Назад",
                            daysOfWeek: [
                                "Вс",
                                "Пн",
                                "Вт",
                                "Ср",
                                "Чт",
                                "Пт",
                                "Сб"
                            ],
                            "monthNames": [
                                "Январь",
                                "Февраль",
                                "Март",
                                "Апрель",
                                "Май",
                                "Июнь",
                                "Июль",
                                "Август",
                                "Сентябрь",
                                "Октябрь",
                                "Ноябрь",
                                "Декабрь"
                            ],
                            "firstDay": 1,
                        }, 
                    }, function(start, end) {
                        let weekDaySelect = row.find('.list-cell__{$this->weekday}').find('select');
                        weekDaySelect.prop('selectedIndex', (start.day() == 0) ? 6 : start.day() - 1);
                        let startDateInput = row.find('.list-cell__{$this->start}').find('input');
                        let endDateInput = row.find('.list-cell__{$this->end}').find('input');
                        startDateInput.val(start.format('DD.MM.YYYY HH:mm'));
                        endDateInput.val(end.format('DD.MM.YYYY HH:mm'));
                    });
                    $('[data-range-key="Custom Range"]').text('Период');
                } else {
                    let dateRangeList = row.find('.list_date_range');
                    dateRangeList.each((index, element) => {
                        $(element).replaceWith($('<input type="hidden" id="list_date_range">'));
                    });
                    row.find('.picker').each((index, element) => {
                        let picker = $(element);
                        let value = $(element).val();
                        let newPicker = $('<input type="time" class="picker form-control" value="' + value + '">');
                        picker.replaceWith(newPicker);
                        newPicker.timepicker();
                    })
                }
            });
        });

        $('#multiple-input').on('afterAddRow', function(e, row, currentIndex){
            let pickers = row.find('.picker');
            pickers.each((index, element) => {
                let picker = $(element);
                let newPicker = $('<input type="time" class="picker form-control">');
                picker.replaceWith(newPicker);
                newPicker.timepicker();

            });
            
            let dateRangeList = row.find('td.list-cell__list_date_range i');
            let dateRangeEmpty = $('<i></i>');
            dateRangeList.replaceWith(dateRangeEmpty);
            
        });

        $('table.multiple-input-list').on('change', '.list-cell__{$this->status} select', function() {
            const selectedStatus = $(this).val();
            const row = $(this).closest('tr');
            let pickers = row.find('.picker');
            if (selectedStatus === '2') {
                pickers.each((index, element) => {
                    let picker = $(element);
                    let newPicker = $('<input type="text" class="picker form-control">');
                    picker.replaceWith(newPicker);
                    if (index == 0){
                        newPicker.datetimepicker({language:'ru',autoclose:true}).on('hide',function(e){
                            let weekDay = moment(e.date).day();
                            let converted = (weekDay == 0) ? 6 : weekDay - 1;
                            row.find('td.list-cell__{$this->weekday}').find('select').prop('selectedIndex', converted);
                        });
                    }else{
                        newPicker.datetimepicker({language:'ru',autoclose:true});
                    }
                });

                let dateRangeList = row.find('#list_date_range');
                let dateRangePickerId = 'date_range_'+ Date.now();
                let dateRangePlugin = $('<i id="' + dateRangePickerId + '" class="fas fa-calendar-alt list_date_range" style="cursor:pointer"></i>');
                dateRangeList.replaceWith(dateRangePlugin);
                dateRangePlugin.daterangepicker({
                    format: 'DD.MM.YYYY',
                    showDropdowns: true,
                    timePicker: true,
                    timePicker24Hour:true,
                    ranges: {
                       'Сегодня': [moment().subtract(1, 'days'), moment()],
                       'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                       '6 дней': [moment().subtract(6, 'days'), moment()],
                       '30 дней': [moment().subtract(29, 'days'), moment()],
                       'Этот месяц': [moment().startOf('month'), moment().endOf('month')],
                       'Предыдущий месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    locale: {
                        format: 'DD.MM.YYYY',
                        separator: ' - ',
                        applyLabel: "Сохранить",
                        cancelLabel: "Назад",
                        daysOfWeek: [
                            "Вс",
                            "Пн",
                            "Вт",
                            "Ср",
                            "Чт",
                            "Пт",
                            "Сб"
                        ],
                        "monthNames": [
                            "Январь",
                            "Февраль",
                            "Март",
                            "Апрель",
                            "Май",
                            "Июнь",
                            "Июль",
                            "Август",
                            "Сентябрь",
                            "Октябрь",
                            "Ноябрь",
                            "Декабрь"
                        ],
                        "firstDay": 1,
                    }, 
                }, function(start, end) {
                    let weekDaySelect = row.find('.list-cell__{$this->weekday}').find('select');
                    weekDaySelect.prop('selectedIndex', (start.day() == 0) ? 6 : start.day() - 1);
                    let startDateInput = row.find('.list-cell__{$this->start}').find('input');
                    let endDateInput = row.find('.list-cell__{$this->end}').find('input');
                    startDateInput.val(start.format('DD.MM.YYYY HH:mm'));
                    endDateInput.val(end.format('DD.MM.YYYY HH:mm'));
                });
                $('[data-range-key="Custom Range"]').text('Период');
            } else {
                let dateRangeList = row.find('.list_date_range');
                dateRangeList.each((index, element) => {
                    $(element).replaceWith($('<input type="hidden" id="list_date_range">'));
                });
                pickers.each((index, element) => {
                    let picker = $(element);
                    let newPicker = $('<input type="time" class="picker form-control">');
                    picker.replaceWith(newPicker);
                    newPicker.timepicker();
                })
            }
        });
JS;
        $this->getView()->registerJs($js);
    }

}