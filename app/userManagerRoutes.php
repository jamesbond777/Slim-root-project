<?php
use App\Controllers\AuthentificationController;

//------------------------------------------------
//           AUTHENTIFICATION  PROCESS
//------------------------------------------------
$app->get('/login', AuthentificationController::class.':login')->setName('login');
$app->get('/logout', AuthentificationController::class.':logout')->setName('logout')->add(\Middlewares\UserManager\LogoutMiddleware::class);
$app->post('/login_check', AuthentificationController::class.':loginCheck')->setName('postlogin')->add(\Middlewares\UserManager\AuthMiddleware::class);

//------------------------------------------------
//              REGISTRATION PROCESS
//------------------------------------------------
$app->get('/register', AuthentificationController::class.':register')->setName('register');
$app->get('/registration/token/{id}', AuthentificationController::class.':confirmUser')->setName('confirm_action')->add(\Middlewares\UserManager\AuthMiddleware::class);
$app->post('/new_registration', AuthentificationController::class.':registrationCheck')->setName('registration')->add(\Middlewares\UserManager\RegistrationMiddleware::class);

//------------------------------------------------
//              PASSWORD PROCESS
//------------------------------------------------
// saisie de l'email de l'utilisateur
$app->get('/forgot_password', AuthentificationController::class.':forgotPassword')->setName('forgot_password');
// Vérification de l'email et envoi du mail
$app->post('/reset_password', AuthentificationController::class.':passwordReset')->setName('resetPass')->add(\Middlewares\UserManager\ResetPasswordMiddleware::class);
// Récupération du lien généré dans le mail et vérification de la validité du token
$app->get('/reset_password/{token}', AuthentificationController::class.':confirmPass')->setName('confirm_pass');
// Redirection sur le formulaire du nouveau mdp
$app->get('/update_password/{id}/{token}', AuthentificationController::class.':UpdatePass')->setName('update_Pass');
// Vérification et traitement des informations saisies
$app->post('/new_password/{id}/{token}', AuthentificationController::class.':changePassword')->setName('passChanged')->add(\Middlewares\UserManager\UpdatePasswordMiddleware::class);