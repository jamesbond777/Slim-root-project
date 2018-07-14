<?php
use App\Controllers\PagesController;
use App\Controllers\DocController;

// Récupération des routes permettant la gestion des utilisateurs
include __DIR__."/userManagerRoutes.php";

//------------------------------------------------
//                ROUTES PROJECT
//------------------------------------------------
$app->get('/', PagesController::class.':home')->setName('home');
$app->get('/documentation/slim', DocController::class. ':documentation')->setName('documentation');
$app->get('/test/image', PagesController::class. ':test')->setName('test');
$app->post('/add/image', PagesController::class. ':newImage')->setName('newImage');

//------------------------------------------------
//                ADMIN ROUTES
//------------------------------------------------
$app->group('/admin', function () {
    $this->get('/dashboard', PagesController::class . ':admin')->setName('admin-interface');
    $this->get('/user/info', PagesController::class . ':adminUserInfo')->setName('admin-user-info');
});

//------------------------------------------------
//                USER ROUTES
//------------------------------------------------
$app->group('/user', function () {
    $this->get('/dashboard', PagesController::class . ':user')->setName('user-interface');
    $this->get('/user/info', PagesController::class . ':userInfo')->setName('user-info');
});

$app->get('/super-admin', PagesController::class . ':super-admin')->setName('super-admin-interface');
