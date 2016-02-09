<?php

return [
	'components' => [
        'multiparser'=>[
            'class' => 'yii\multiparser\YiiMultiparser',
        ],
	],
	'params' => [
        'scenarios_config' => require(__DIR__ . '/scenarios_config.php'),
	],
];
