<?php
namespace Middlewares\UserManager;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class UserConnectedMiddleware {

    /**
     * @Var \Twig_Environment
     */
    private $twig;
    private $container;

    public function __construct(\Twig_Environment $twig, Container $container)
    {
     $this->twig = $twig;
     $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if (isset($_COOKIE['user_connected'])) {
            $this->twig->addGlobal('username', isset($_COOKIE['user_connected']['login']) ? $_COOKIE['user_connected']['login'] : null);
            $this->twig->addGlobal('user_role', isset($_COOKIE['user_connected']['role']) ? $_COOKIE['user_connected']['role'] : null);
        }
        //Enregistrons les informations du dernier utilisateur

        if (isset($_COOKIE['remember']) and $_COOKIE['remember'] === "on"){
            $this->twig->addGlobal('last_connexion', [
                'login' => isset($_COOKIE['user_connected']['login']) ? $_COOKIE['user_connected']['login'] : null,
                'password' => isset($_COOKIE['user_connected']['password']) ? $_COOKIE['user_connected']['password'] : null,
                'role' => isset($_COOKIE['user_connected']['role']) ? $_COOKIE['user_connected']['role'] : null,
                'remember' => isset($_COOKIE['remember']) ? $_COOKIE['remember'] : null,
            ]);
        }
        $response = $next($request, $response);

        return $response;
    }
}