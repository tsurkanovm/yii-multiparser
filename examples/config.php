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
                                "string" => ['DESCR', 'BRAND'],
                                "float" => 'PRICE',
                                "integer" => ['BOX', 'ADD_BOX'],
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
        ['custom' =>
            ['class' => 'yii\multiparser\XlsxParser',
                'path_for_extract_files' => $_SERVER["DOCUMENT_ROOT"] . '/tests/_data/xlsx_tmp/',
                'converter_conf' => [
                    'class' => 'yii\multiparser\Converter',
                    'configuration' => ["encode" => []],
                ]
            ],
            'template' =>
                ['class' => 'yii\multiparser\XlsxParser',
                    'path_for_extract_files' => $_SERVER["DOCUMENT_ROOT"] . 'tests/_data/xlsx_tmp',
                    'keys' => [
                        0 => 'Original',
                        1 => 'Replacement',
                    ],
                 ],
            'basic_column' => [
                Null => 'null',
                "Description" => 'Название',
                "Article" => 'Артикул',
                "Price" => 'Цена',
                "Brand" => 'Производитель',
                "Count" => 'Количество',
            ],
        ],
];

