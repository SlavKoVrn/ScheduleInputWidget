<?php
use yii\helpers\Html;
?>
<?= Html::beginTag('div'); ?>
        <div>
            <?= $hiddenInput; ?>
            <?= $element; ?>
        </div>

<?php
$this->registerJs(<<<JS
(function($) {
      function addNewDiv() {
          console.log('addNewDiv');
      }
      $('.sx-btn-add-row').click(addNewDiv);
}(jQuery));
JS
); ?>
<?= Html::endTag('div'); ?>