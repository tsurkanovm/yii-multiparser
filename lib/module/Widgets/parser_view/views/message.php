<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use common\modules\parser\widgets\parser_view\ParserViewAsset;

$title = '';
if ( !empty( $params['title'] ) ) {
    $title = $params['title'];
}
echo Html::tag( 'h4', $title );
?>

