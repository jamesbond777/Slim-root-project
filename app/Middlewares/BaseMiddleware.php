<?php
namespace App\Middlewares;

use Slim\Container;

class BaseMiddleware {

    protected $container;
    protected $entityManager;
    /**
     * @Var \Twig_Environment
     */
    private $twig;

    /**
     * BaseMiddleware constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->entityManager = $this->container['getEntityManager'];
        $this->twig = $container->get('view')->getEnvironment();
    }

    /**
     * @param $loginValue
     * @return bool
     */
    public function checkUserConnect($loginValue){
        if (isset($_COOKIE['user_connected']) and $_COOKIE['user_connected']['login'] === $loginValue){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @return null
     */
    public function getUserConnect(){
        return $_COOKIE['user_connected'];
    }

    /**
     * @return bool
     */
    public function logout(){
        $_SESSION['userExist'] = false;
        setcookie("user_connected[login]", "visitor");
        setcookie("user_connected[role]", "ANONYMOUS");
        return true;
    }

    /**
     * @return mixed
     */
    public function getSecurityLogin(){
        // Récupération des paramètres de sécurités
        $security = $this->container['security'];
        return $security['AuthenticateWith']['login'];
    }

    /**
     * @return mixed
     */
    public function getSecurityConfig(){
        // Récupération des paramètres de sécurités
        $security = $this->container['security'];
        return $security['config'];
    }


    /**
     * @return mixed
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param string $message
     * @param string $type
     * @return mixed
     */
    public function flash($message, $type = 'success'){
        if(!isset($_SESSION['flash'])){
            $_SESSION['flash'] = [];
        }
        return $_SESSION['flash'][$type] = $message;
    }

}