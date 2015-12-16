#YII2 Multiparser
==================


Flexible bunch of parsers for YII 2.

##Requirements##

The Multiparser library has the following requirements:

 - artweb/multiparser

##Documentation##

###1.	Общие сведения###
Парсер позволяет отпарсить содержимое файла в массив. Парсер поддерживает csv, xml, xls, xlsx, txt расширения. Для каждого расширения необходимо описать правила парсинга в конфигурационном файле (см. п.4). Для одного расширения можно указать несколько сценариев (использование двух сценариев описано в п.3. в вложенном примере к парсеру).
###2.	Установка###
После копирования пакета в проект необходимо установить парсер как компонент YII. Для этого необходимо составить конфигурационный файл – config.php. Примерами могут служить файл который вложен в пакет или конфигурационный файл, который скомпонован для работы примера (п. 3). Далее в файле common/config/main.php – добавить компонент:

```php
$mp_configuration = require(path to config.php);
return [
    …
	…
        'multiparser'=>[
            'class' => 'yii\multiparser\YiiMultiparser',
            'configuration' => $mp_configuration,
        ],
    ],
];
```
После этого парсер можно запускать следующим образом:
```php
$data = Yii::$app->multiparser->parse( file_path );
```

###3.	Установка примера.###
###4.	Описание конфигурационного файла.###
###5.	Дополнительные возможности.###



