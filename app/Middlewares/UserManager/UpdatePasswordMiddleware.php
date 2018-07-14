<?php
namespace Middlewares\UserManager;

use Respect\Validation\Validator;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class UpdatePasswordMiddleware {

    /**
     * @Var \Twig_Environment
     */
    private $twig ;
    /**
     * @var Container
     */
    private $container;


    public function __construct(Container $container)
    {
        $this->twig = new \Twig\Environment();
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        //verifions l'existance de l'utilisateur
        $result = $this->changePass($request, $response);

        if(empty($result['errors'])){
            $_SESSION['success'] = $result;
        }else{
            $_SESSION['success'] = $result;
        }

        $response = $next($request, $response);

        return $response;
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

    /**
     * @return mixed
     */
    public function getTokenValidityTime(){
        // Récupération des paramètres de sécurités
        $security = $this->container['security'];
        return $security['TokenValidityTime'];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return array
     */
    public function changePass(Request $request, Response $response){
        $errors = [];
        if ($request->getParam('_password') === $request->getParam('_password_confirmation')){
            Validator::notEmpty()->validate($request->getParam('_password')) || $errors['password'] = 'Veuillez saisir un mot de passe correcte';
            Validator::notEmpty()->validate($request->getParam('_password_confirmation')) || $errors['password_confirmation'] = 'Veuillez confirmer votre mot de passe';
        }else{
            $errors['invalide'] = 'erreur dans la saisie du mot de passe !';
        }
        $result = [
            'user' => null,
            'errors' => $errors
        ];

        return $result;
    }

}