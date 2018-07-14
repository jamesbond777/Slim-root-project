<?php

    $container = $app->getContainer();

    //------------------------------------------------
    //                  Settings
    //------------------------------------------------
    $container['parameters'] = function (){
        $config = include(__DIR__ . '/../config/settings.php');
        return $config['settings'];
    };

    //------------------------------------------------
    //                  Security
    //------------------------------------------------
    $container['security'] = function (){
        $security = include(__DIR__ . '/../config/security.php');
        return $security['config'];
    };

    //------------------------------------------------
    //                  Debug init
    //------------------------------------------------
    $container['debug'] = function ($container){
        return $container['parameters']['displayErrorDetails'];
    };

    //------------------------------------------------
    //                  Token CSRF
    //------------------------------------------------
    $container['csrf'] = function () {
        return new \Slim\Csrf\Guard;
    };

    //------------------------------------------------
    //                  PDO CONNECTION
    //------------------------------------------------
    $container['pdo'] = function ($container) {
        $parameter = $container['parameters']['doctrine']['connection'];
        try{
            $host = $parameter['host'];
            $dbname = $parameter['dbname'];
            $user = $parameter['user'];
            $mdp = $parameter['password'];

            $db = new PDO('mysql:'.$host.''.$dbname.';charset=UTF8;', ''.$user.'', ''.$mdp.'');
            $db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }catch(Exception $e){
            echo "Aie, erreur : $e->getMessage";
        }
        return $db;
    };


    //------------------------------------------------
    //                   Twig init
    //------------------------------------------------
    $container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__.'/Resources', [
        'cache' => $container['debug'] ? false : __DIR__.'/../var/cache',
        'debug' => $container['debug']
    ]);
    //Extension permettant d'utiliser var_dump dans les vues twig
    if ($container->debug){
        $view->addExtension(new Twig_Extension_Debug());
    }
//    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $basePath = $container['request']->getUri()->getBasePath();
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
    };

    //------------------------------------------------
    //               Swiftmailer init
    //------------------------------------------------
    $container['mailer'] = function ($container){

    // Récupération des parametres
    $options = $container['parameters'];
    $options = $options['swiftmailer'];
    if($options['server_type'] === 'GMAIL'){

        //  Création du "moyen de transport" (via serveur GMAIL)
        $transport = Swift_SmtpTransport::newInstance($options['host'], $options['port'], $options['security'])
            ->setUsername($options['username'])
            ->setPassword($options['password']);

    } elseif ($options['server_type'] === 'SMTP'){

        //  Création du "moyen de transport" (via serveur SMTP)
        $transport = Swift_SmtpTransport::newInstance($options['host'], $options['port'])
            ->setUsername($options['username'])
            ->setPassword($options['password']);
    }
    $mailer = Swift_Mailer::newInstance($transport);
    return $mailer;
    };


    //------------------------------------------------
    //                 Doctrine ORM
    //------------------------------------------------
    // Create entity Manager
    $container['createEntityManager'] = function ($container){

        // Récupération des parametres
        $options = $container['parameters']['doctrine'];
        $devMode = $container->debug;

        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($options['meta']['entity_path'], $devMode);

        return \Doctrine\ORM\EntityManager::create($options['connection'], $config);
    };

    // Get entity Manager
    $container['getEntityManager'] = function ($container){

        $entityManager = $container->createEntityManager;

        return $entityManager;
    };


    //------------------------------------------------
    //                Uploaded files
    //------------------------------------------------
    $container['upload_directory'] = function () {
        return __DIR__ . '/../web/upload';
    };


    //------------------------------------------------
    //               Monolog Init
    //------------------------------------------------
    $container['Logger'] = function() {
        $logger = new Monolog\Logger('logger');
        $filename = __DIR__ . '/../var/log/error.log';
        $stream = new Monolog\Handler\StreamHandler($filename, Monolog\Logger::DEBUG);
        $fingersCrossed = new Monolog\Handler\FingersCrossedHandler(
            $stream, Monolog\Logger::ERROR);
        $logger->pushHandler($fingersCrossed);

        return $logger;
    };

    // Error handler
    $container['errorHandler'] = function ($container) {
        return new App\Handlers\Error($container['Logger']);
    };


    //------------------------------------------------
    //               Handler Error
    //------------------------------------------------
    // Not Found Error handler | 404 ERROR
    $container['notFoundHandler'] = function ($container) {
        return new App\Handlers\NotFoundHandler($container);
    };

//     Not Found Error handler | 500 ERROR
    if ($container['parameters']['displayErrorDetails'] === false){
        $container['phpErrorHandler'] = function ($container) {
        return new App\Handlers\PhpErrorHandler($container);
        };
    }

    // Not Allowed Error handler | 405 ERROR
    $container['notAllowedHandler'] = function ($container) {
        return new App\Handlers\notAllowedHandler($container);
    };


