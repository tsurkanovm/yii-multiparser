YII2 Multiparser
==================
Universal module for YII 2 that provides parsing and writing user files (csv, xml, xls, xlsx, txt) to DB with a flexible settings.

##Requirements##

The Multiparser library has the following requirements:

 - yiisoft/yii2

##Документация##

###1.	Общие сведения.###
Модуль позволяет прочитать содержимое выбранного файла в массив и отбразить его на экране для дальнейшей записи его в базу данных. Пользователь должен выбрать в форме соответсвие колонок, а также с какой по какую строку (необязательно) необходимо грузить данные.
 Парсер поддерживает чтение csv, xml, xls, xlsx, txt файлов. Для каждого расширения можно указать свои правила парсинга (см. п. 4). Для каждого приемника данных (например одной или нескольких таблиц БД) в настройках указывается свой класс (writer). Для каждого приемника выделяется отдельный сценарий в которм прописываются правила чтения и записи данных.
###2.	Установка модуля.###
В состав пакета входит пример развернутого модуля. Для ознакомления с возможностями модуля (и дальнейшего его использования) рекомендуется использовать вложенный пример.
После установки пакета в проект необходимо скопировать папку examples/modules в папку common (для advanced приложения) и зарегистрировать модуль в конфигурационном файле:
```php
...
 'modules' => [
        'parser' => [
            'class' => 'common\modules\parser\Module',
        ],
    ],
 ...
```
Что бы пример работал корректно - необходимо применить миграцию которая описана в examples/migration. В качетстве примера приемника здесь применена одна простая таблица. В реальных проектах приемником могут выступать любые системы хранения данных.
Для данного источника описан простейший механизм записи данных - examples\modules\parser\components\DetailsWriter.php.
Все сценарии определяются в файле - examples\modules\parser\scenarios_config.php.
Для вышеописанного примера файл сценариев выглядит следующим образом:

```php
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
```
На первом уровне массива указываются сценарии. В данном примере у нас один сценарий - details. Далее следуют параметры чтения и записи данных, которые будут описаны ниже.


###3.	Настройки записи данныха.###
'basic_columns' - колонки которые будут показаны пользователю что бы он выбрал соответсвие. В качетсве ключей этого массива должны быть указаны поля приемника.
'require_columns' - обязательные поля. Валидатор будет проверять наличие соответсвия как минимум этих полей.
'writer' - класс который осуществляет запись данных в БД.
Для упрощения работы с записью данных в состав пакета входит абстрактный класс Writer который уже содержит большинство функций записи и валидации.
А также MassiveDataSQLBuilder - это класс Behaviour, который содержит функции вставки и обновления больших массивов данных прямымми запросами в MySQL DB. Подключение данного класса к модели Active Record ьожно выполнить следующим образом:
```php
 public function behaviors()
    {
        return [

            'SQLBuilder' => [
                'class' => MassiveDataSQLBuilder::className(),
                'batch' => 500, // размер пакета вставляемых данных
                'keys' =>  [ // поля которые являются клчевыми - если они дублируются до данные - обновляются а не записываются
                    'article',
                    'brand',
                ],
            ]
        ];
    }
```
###4.	Настройки чтения (парсинга. файлов###

'parser_config' - это описание многомерного массива настроек парсинга данных.
    <p>4.1. Элементами первого уровня массива указываются расширения файлов, с которыми компонент будет работать. Для каждого расширения определяется массив настроек парсинга данного типа файлов. Пакет поддерживает определение нескольких сценариев (несколько параллельных настроек) для одного типа файлов.</p> 
<p>4.2. На этом уровне можно определить параметры, которые будут доступны для данного расширения.</p>
В примере используется эта возможность для определения колонок выбора соответствия отпарсенных колонок с эталонными колонками (параметр - basic_column). Вызов этого параметра можно осуществить следующим образом:
```php
Yii::$app->multiparser->getConfiguration($file_extension, 'basic_column');
```
<p>4.3. На втором уровне указываются настройки парсера в виде конфигурационного массива YII.</p>
Данный массив имеет один обязательный элемент с ключем – class, в котором указывается имя парсера, который будет обрабатывать данный тип файлов. Таким образом можно указать свой класс парсера, или использовать классы входящие в пакет, например для csv это класс - `yii\multiparser\CsvParser`.
При использовании встроенного класса (или наследуемые от него) в данном массиве можно установить следующие атрибуты в качестве настроек: 
        <p>4.3.1. `converter_conf – array`. Настройки конвертера. Детально описано в п.5.</p>
        <p>4.3.2. `keys – array`. В этом параметре можно назначить имена колонкам файла. Например:</p>
        <pre>
        ```php
        'keys' => [
            0 => 'Description',
            1 => 'Article',
            2 => 'Price',
            3 => 'Brand',
            4 => 'Count',
        ]
        ```
        </pre>
При такой настройке результирующий массив будет ассоциативным, где в колонке массива с ключем `'Brand'` – будут значения из четвертой колонки файла.
        <p>4.3.3. `has_header_row – bolean`. Признак, имеет ли файл заголовок в первой значимой строке, если true - первая значимая строка будет пропущена и не попадет в результирующий массив. По умолчанию – true.</p>
        <p>4.3.4. `first_line – integer`. Номер строки с которой начинается парсинг. Если установлен аттрибут has_header_row, тогда следующая строка за данной, будет считаться заголовком и будет пропущена. По умолчанию – 0.</p>
        <p>4.3.5. `last_line – integer`. Номер строки по которую будет произведен парсинг. Если = 0, парсинг будет производится до конца файла. По умолчанию – 0.</p>
        <p>4.3.6. `min_column_quantity  - integer`. Минимальное количество заполненных колонок строки файла, что бы она попала в результирующий массив. Если строка имеет меньше заполненных колонок чем указано в параметре, данная строка пропускается. По умолчанию – 3.</p>
        <p>4.3.7. `empty_lines_quantity - integer`. Количество пустых строк, что бы определить конец файла. Имеет смысл только при last_line =0. По умолчанию – 3. То есть парсинг закончится на строке, после которой встретятся три пустых строки.</p>

Если парсер зарегистрировать как компонет приложения то его можно запускать следующим образом:
```php
$data = Yii::$app->multiparser->parse( file_path );
```
В вложенном примере парсер зарегистрирован как компонент модуля в файле examples/modules/parser/config.php, поэтому в пределах модуля обращаться к нему можно так:
```php
        // get parser component
        $parser = Yii::$app->controller->module->multiparser;
        // setup configuration
        $parser->setConfiguration( $parser_config );
        // run parser
        $data = $parser->parse( $file_path, $custom_settings );
```

###5.	Конвертер.###
В состав пакета входит конвертер который позволяет осуществлять преобразования прочитанных данных в процессе парсинга. Таким образом можно получить очищенные и преобразованные данные в результирующем массиве. Простейшим примером, такого преобразования может служит смена кодировки. По умолчанию конвертер входящий в пакет осуществляет смену кодировки с 'windows-1251' в 'UTF-8'.
Конвертер представляет собой отдельный класс с статическими методами конвертации значений. Что бы подключить конвертер к парсеру, необходимо заполнить свойство конфигурационного файла converter_conf.
Данное свойство является  конфигурационным массивом с двумя обязательными элементами. Элемент с ключем class, в котором необходимо указать класс используемого конвертера. И Элемент с ключем configuration – массив ключи которого описывают методы преобразования, а значения – имена колонок файла для преобразования.
Конвертер входящий в пакет содержит следующие методы преобразования:
    <p>5.1. Encode – метод меняет кодировку с 'windows-1251' в 'UTF-8'.</p>
    <p>5.2. String – метод очищает строку от специальных символов. А именно, удаляются - `!, @, #, $, %, ^, &, *, (, ), _, +, =, -, ~, ```, ", ', №, %, ;, :, [, ], {, }, *, ?, /, \ , |, ., ',' , <, >, \.`</p>
    <p>5.3. Integer – метод преобразует строку в Integer.</p>
    <p>5.4. Float – метод преобразует строку в float.</p>
Конкретные значения для конвертации есть смысл указывать только если в настройках парсера указаны ключи соответствия (свойство key см. 4.3.2). Иначе необходимо указать в качестве значения – пустой массив, что будет означать применение данного метода для всех колонок файла.
Допустимо указывать колонки для конвертации тремя способами:
```php
'configuration' => ['encode' => [], // - конвертация всех колонок файла методом 'encode'
    'string' => ['Description', 'Brand'], - конвертация только двух колонок методом 'string'
    'integer' => 'Count' – конвертация колонки 'Count' методом 'integer'
]
```
Расширение конвертера.
Для добавления своих методов преобразования необходимо создать свой класс конвертера. Это можно сделать путем наследования от базового класса конвертера или реализовав интерфейс ConverterInterface. В данном классе реализовать статические методы преобразования.



