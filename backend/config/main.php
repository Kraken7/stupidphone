<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'language' => 'ru',
    'name' => 'stupid phone',
    'modules' => [
        'v1' => [
            'class' => 'backend\modules\v1\Module',
            'layout' => false,
            'defaultRoute' => 'main/index',
        ],
    ],
    'components' => [
        'formatter' => [
            'datetimeFormat' => 'dd.MM.yyyy HH:mm:ss',
            'dateFormat' => 'dd.MM.yyyy',
        ],
        'request' => [
//            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => '1hy5PHnC5HBx0-sgU2QYLYxzJomAdT37',
            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => '/v1/main/unauthorized',
            'identityCookie' => [
                'name' => '_identity',
                'httpOnly' => true,
                'path' => '/',
                'domain' => $params['sessionDomain'],
            ],
//            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
//            'name' => 'advanced-backend',
            'cookieParams' => [
                'domain' => $params['sessionDomain']
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
                'collapseSlashes' => true,
                'normalizeTrailingSlash' => true,
            ],
            'rules' => [
                // rest api
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/game',
                    'extraPatterns' => [
                        'GET' => 'index',
                        'GET /{id}' => 'view',
                        'GET current' => 'current',
                        'POST entry' => 'entry',
                        'POST exit' => 'exit',
                        'POST' => 'create',
                        'POST start' => 'start',
                        'POST stop' => 'stop',
                    ],
                    'except' => ['delete', 'update'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/invite',
                    'extraPatterns' => [
                        'GET' => 'index',
                        'POST' => 'create',
                        'POST ok/<id:\d+>' => 'ok',
                        'POST cancel/<id:\d+>' => 'cancel',
                    ],
                    'except' => ['delete', 'update'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/message',
                    'extraPatterns' => [
                        'GET' => 'index',
                        'POST' => 'create',
                        'POST <id:\d+>' => 'view',
                    ],
                    'except' => ['delete', 'update'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/review',
                    'extraPatterns' => [
                        'GET' => 'index',
                        'POST' => 'create',
                    ],
                    'except' => ['delete', 'update'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/repost',
                    'extraPatterns' => [
                        'POST' => 'create',
                    ],
                    'except' => ['delete', 'update'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/user',
                    'extraPatterns' => [
                        'GET my' => 'my',
                        'PUT my' => 'edit-token'
                    ],
                    'except' => ['delete', 'update'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/phrase',
                    'extraPatterns' => [
                        'GET' => 'index',
                        'POST answer' => 'answer',
                        'POST question' => 'question',
                    ],
                    'except' => ['delete', 'update'],
                ],
            ],
        ],

    ],
    'params' => $params,
];
