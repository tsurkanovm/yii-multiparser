<?php
return
    [
        'csv' =>
            ['web' =>
                ['class' => 'artweb\yii_multiparser\CsvParser',
                    'auto_detect_first_line' => true,
                    'converter_conf' => [
                    "float" => 'PRICE',
                    "integer" => 'QUANTITY',
                    "string" => 'DESCR'
                    ]],
                'basic_column' => [
                    "ARTICLE" => 'Артикул',
                    "PRICE" => 'Цена',
                    "DESCR" => 'Наименование',
                    "QUANTITY" => 'Колво'

                ],
            ]];
