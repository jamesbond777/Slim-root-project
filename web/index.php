<?php
session_start();
require '../vendor/autoload.php';
$settings = include(__DIR__ . '\..\config\settings.php');
$app = new Slim\App($settings);

// Chargement des dépendences
require '../app/container.php';

// Chargement des middlewares
require '../app/middlewares.php';

// Chargement des routes
require '../app/router.php';
$app->run();