<?php
//Configuration de l'application
//Envoie de mail via serveur gmail (host='smtp.gmail.com', port= 465, security="ssl")
return [   'settings' => [
                'displayErrorDetails' => true,
                'determineRouteBeforeAppMiddleware' => true,
                'debug' => true,
                'env' => "dev", //Choose environnement dev or prod
                'doctrine' => [
                    'meta' => [
                        'entity_path' => array(__DIR__ . '/../app/Entity'),
                        'auto_generate_proxies' => true,
                        'proxy_dir' =>  __DIR__.'/../var/cache/proxies',
                        'cache' => null,
                        'timezone' => 'Africa/Abidjan',
                    ],
                    'connection' => [
                        'driver'   => 'pdo_mysql',
                        'host'     => '127.0.0.1',
                        'dbname'   => 'slim_bd',
                        'user'     => 'root',
                        'password' => '',
                        'charset'   => 'utf8',
                        'collation' => 'utf8_unicode_ci',
                        'prefix'    => ''
                    ],
                ],
                'swiftmailer' => [
                'host' => 'smtp-interactivstudio.alwaysdata.net',
                'server_type' => 'SMTP',
                'port' => '587',
                'security' => 'ssl',
                'username' => 'interactivstudio@alwaysdata.net',
                'password' => 'coqivoire',
                'setFrom' => 'email@slim.com',
                 ]
            ],

           ];
