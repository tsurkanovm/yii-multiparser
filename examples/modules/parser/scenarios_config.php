<?php
return [
    'details' =>
        ['parser_config' =>
            [   'csv' =>
                ['class' => 'yii\multiparser\CsvParser',
                    'converter_conf' => [
                        'class' => 'yii\multiparser\Converter',
                        'configuration' => ['encode' => []],
                    ],
                ],

                'xls' =>
                    ['class' => 'yii\multiparser\XlsParser',
                        'converter_conf' => [
                            'class' => 'yii\multiparser\Converter',
                            'configuration' => ['encode' => []],
                        ],
                    ],
            ],

            'basic_columns' => [
                Null => 'null',
                'name' => 'Название',
                'article' => 'Артикул',
                'price' => 'Цена',
                'brand' => 'Производитель',
                'count' => 'Количество',
            ],

            'require_columns' => [
                'article',
                'brand',
            ],

            'writer' => 'common\modules\parser\components\DetailsWriter',
            'title' => 'Загрузка товаров',
        ],


];

