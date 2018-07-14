<?php
namespace Middlewares\UserManager;

use App\Entity\Users;
use Respect\Validation\Validator;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class RegistrationMiddleware {

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
        $result = $this->addUser($request, $response);
        if(empty($result['error'])){
            $_SESSION['success'] = $result;
        }else{
            $_SESSION['success'] = null;
        }

        $response = $next($request, $response);

        return $response;
    }

    /**
     * @param Users $user
     * @param $pathSecurity
     * @param $request Request
     * @return int|string
     */
    public function getPathRedirect(Users $user, $pathSecurity, $request)
    {
        $userRole = $user->getRole();
        $routeName = null;
        foreach ($pathSecurity as $path => $role){
            if ($userRole === $role){
                $routesList = $this->container->router->getRoutes();
                foreach ($routesList as $route){
                    if ($route->getPattern() === $path){
                        $routeName = $route->getName();
                    }
                }
                return $routeName;
            }else{
                $this->flash("Vous n'êtes pas autorisé à accéder à cette interface", "error");
                return 'login';
            }
        }
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
    public function getSecurityLogin(){
        // Récupération des paramètres de sécurités
        $security = $this->container['security'];
        return $security['AuthenticateWith']['login'];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return array
     */
    public function addUser(Request $request, Response $response){
        // Récupération de l'entity manager de doctrine
        $em = $this->container['getEntityManager'];
        $errors = [];
        Validator::notEmpty()->validate($request->getParam('_username')) || $errors['username'] = 'Veuillez entrer votre username';
        Validator::email()->validate($request->getParam('_email')) || $errors['email'] = 'Votre email n\'est pas valide';
        Validator::notEmpty()->validate($request->getParam('_password')) || $errors['password'] = 'Veuillez saisir un mot de passe correcte';

        $result = [
            'user' => null,
            'errors' => $errors
        ];
            if (empty($errors)){
                
        if($this->checkBddConnect()){
                //vérifions que l'utilisateur n'existe pas déjà
                $userList = $em->getRepository('App\Entity\Users')->findAll();
                foreach ($userList as $item) {
                    if ($item->getUsername() === $request->getParam('_username') or $item->getEmail() === $request->getParam('_email')){
                        $errors['registration'] = 'Un utilisateur possède déjà un username ou un email identique!';
                        return $result = [
                            'user' => null,
                            'errors' => $errors
                        ];
                    }
                }
                $user = new Users();
                $user->setUsername($request->getParam('_username'));
                $user->setEmail($request->getParam('_email'));
                $user->setPassword($request->getParam('_password'));
                $user->setEnable(0);
                $user->setRole('ROLE_USER');
                $token = hash('whirlpool', $request->getParam('_username').''.$request->getParam('_password'));
                $user->setToken($token);
                $em->persist($user);
                $em->flush();
                $result['user'] = $user;
                
            }else{
                $result = [
                    'user' => null,
                    'errors' => $errors
                ];
            }
        }
        

        return $result;
    }

     /**
     * @return mixed
     */
    public function checkBddConnect(){
        // Récupération des paramètres de sécurités
        $result = true;
        $parameter = $this->container['parameters']['doctrine']['connection'];
        $dbname = $parameter['dbname'];
        $bdd = $this->container['pdo'];
        $stmt = $bdd->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =?');
        $stmt->execute(array($dbname));
        if ($stmt->fetchColumn() == 0) {
            // bdd non existante
            $result = false;
        } else {
            // la bdd  existe
            $result = true;
        }
        return $result;
    }

}