<?php

use yii\helpers\Html;
use yii\multiparser\DynamicFormHelper;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\CatalogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Results';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="catalog-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php

    $form = ActiveForm::begin(['action' => 'write']);

    if (empty( $header_model )) {
        // выведем просто массив без колонок выбора
    echo \yii\grid\GridView::widget([
        'dataProvider' => $dataProvider,
      //  'layout'=>"{pager}\n{items}",

    ]);

    } else {
        echo DynamicFormHelper::CreateGridWithDropDownListHeader($dataProvider, $form, $header_model, $basic_column);
    }
    ?>

    <?php ActiveForm::end() ?>
    <?= Html::a('Return', ['parser/index'], ['class' => 'btn btn-primary', 'name' => 'Return',]) ?>

</div>