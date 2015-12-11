<?php
return [
    'csv' =>
        ['custom' =>
            ['class' => 'yii\multiparser\CsvParser',
                'converter_conf' => [
                    'class' => 'yii\multiparser\Converter',
                    'configuration' => ["encode" => []],
                ]
            ],
            'template' =>
                ['class' => 'yii\multiparser\CsvParser',
                    'keys' => [
                        0 => 'Description',
                        1 => 'Article',
                        2 => 'Price',
                        3 => 'Brand',
                        4 => 'Count',
                    ],
                    'converter_conf' => [
                        'class' => 'yii\multiparser\Converter',
                        'configuration' => ["encode" => 'Description',
                            "string" => ['Description', 'Brand'],
                            "float" => 'Price',
                            "integer" => 'Count'
                        ]
                    ],],

            'basic_column' => [
                Null => 'null',
                "Description" => 'Название',
                "Article" => 'Артикул',
                "Price" => 'Цена',
                "Brand" => 'Производитель',
                "Count" => 'Количество',
            ],
        ],
    'xml' =>
        ['custom' =>
            ['class' => 'yii\multiparser\XmlParser',
                'converter_conf' => [
                    'class' => 'yii\multiparser\Converter',
                    'configuration' => ["encode" => []],
                ]
            ],
            'template' =>
                ['class' => 'yii\multiparser\XmlParser',
                    'node' => 'Товар',
                    'has_header_row' => false,
                    'keys' => [
                        "BRAND" => 'Производитель',
                        "ARTICLE" => 'Код',
                        "PRICE" => 'Розница',
                        "DESCR" => 'Наименование',
                        "BOX" => 'Колво',
                        "ADD_BOX" => 'Ожидаемое',
                        "GROUP" => 'Группа'
                    ],
                    'converter_conf' => [
                        'class' => 'yii\multiparser\Converter',
                        'configuration' => [
                            'converter_conf' => [
                            'class' => 'yii\multiparser\Converter',
                            'configuration' => ["encode" => 'DESCR',
                                "string" => ['DESCR', 'BRAND'],
                                "float" => 'PRICE',
                                "integer" => ['BOX', 'ADD_BOX'],
                            ],
                        ],
                        ],
                    ],
                ],
            'basic_column' => [
                Null => 'null',
                "BRAND" => 'Производитель',
                "ARTICLE" => 'Код',
                "PRICE" => 'Розница',
                "DESCR" => 'Наименование',
                "BOX" => 'Колво',
                "ADD_BOX" => 'Ожидаемое',
                "GROUP" => 'Группа'
            ],
        ],
    'xlsx' =>
        ['web' =>
            ['class' => 'common\components\parsers\XlsxParser',
                //         'path_for_extract_files' => \Yii::getAlias('@temp_upload') . '/xlsx/',
                //'auto_detect_first_line' => true,
                //'has_header_row' => true,
                'active_sheet' => 1,
                'converter_conf' => [
                    'class' => 'common\components\parsers\CustomConverter',
                    'configuration' => ["string" => []],
                ]
            ],
        ]
];

