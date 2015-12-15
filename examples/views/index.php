<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

?>
<div class="row">
    <div class="col-lg-5">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data',],'action'=>['parser/read']]);
        ?>
        <h3>Choose file to parse</h3>

        <?= $form->field($model, 'file')->fileInput() ?>

        <?= $form->field($model, 'file_type')->radioList([0 => 'Custom file', 1 => 'csv template', 2 => 'xml template', 3 => 'xlsx template', 4 => 'xls template', 5 => 'txt template'])->label(false);
        ?>

        <div class="form-group">
            <?= Html::submitButton('Read', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end();
        ?>
    </div>
</div>

