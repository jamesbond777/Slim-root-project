<?php
// Middlewares
$app->add(new \App\Middlewares\FlashMiddleware($container->get('view')->getEnvironment()));
$app->add(new \App\Middlewares\OldMiddleware($container->get('view')->getEnvironment()));
$app->add(new \App\Middlewares\TwigCsrfMiddleware($container->get('view')->getEnvironment(), $container->get('csrf')));

//------------------------------------------------
//              USER MANAGER PROCESS
//------------------------------------------------
$app->add(new \Middlewares\UserManager\AuthMiddleware($container));
$app->add(new \Middlewares\UserManager\UserConnectedMiddleware($container->get('view')->getEnvironment(),$container));
//$app->add($container->get('csrf'));