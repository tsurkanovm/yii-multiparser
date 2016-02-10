<?php
use yii\widgets\ActiveForm;
use \yii\multiparser\DynamicFormHelper;
use \yii\helpers\Html;

    $form = ActiveForm::begin(['action'=>$action_write, 'options' => ['class' => 'form-inline', 'id' => 'write-form']]);
\yii\widgets\Pjax::begin();
echo DynamicFormHelper::CreateGridWithDropDownListHeader($dataProvider, $form, $header_model, $basic_column);
\yii\widgets\Pjax::end();
echo $form->field($write_model, 'write_line_begin',['options' =>['class' => 'col-md-6']]);
echo $form->field($write_model, 'write_line_end',['options' =>['class' => 'col-md-6']]);
echo $form->field($write_model, 'update',['options' =>['hidden' => true]]);
echo Html::tag('div', Html::submitButton('Обновить', ['class' => 'btn btn-primary', 'id' => 'update']),['class' => 'form-group col-md-2']);
echo Html::tag('div', Html::submitButton('Записать', ['class' => 'btn btn-primary', 'id' => 'write']),['class' => 'form-group col-md-2']);

    ActiveForm::end();
