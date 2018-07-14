<?php
namespace Middlewares\UserManager;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthMiddleware {

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
        $em = $this->container->get('getEntityManager');
        $configLogin = strtolower($this->getSecurityLogin());
        $login = $request->getParam('_'.$configLogin);

        //verifions l'existance de l'utilisateur
        $result = $this->checkUserExist($request, $response);
        if($result === true){
            $_SESSION['userExist'] = true;
        }elseif($result === false){
            $_SESSION['userExist'] = false;
        }
        if ($_SESSION['userExist'] === true){
            $user = $em->getRepository('App\Entity\Users')->findOneBy([$configLogin => $login]);
            setcookie("user_connected[login]", ($login !== null) ? $login : null);
            setcookie("user_connected[password]", isset($_SESSION['userpass']) ? $_SESSION['userpass'] : null);
            setcookie("user_connected[role]", ($user->getRole()) ? $user->getRole() : 'ANONYMOUS');
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
     * @return bool
     * @throws \Exception
     */
    public function checkUserExist(Request $request, Response $response){
        // Récupération des paramètres de sécurités
        $security = $this->container['security'];
        //Initialisation
        $checkUser = false;
        $entityName = $this->getEntityName();
        // Récupération de l'entity manager de doctrine
        $em = $this->container['getEntityManager'];
        $mdp = $request->getParam('_password');
        $result = $this->checkBddConnect();
        if($result){
            if ($request->getParam('_remember') === 'on'){
                setcookie("remember", "on");
            }else{
                setcookie("remember", "");
            }
            $_SESSION['userpass'] = $mdp;
            if ($security['AuthenticateWith']['login'] !== '' ){
                $item = $security['AuthenticateWith']['login'];
                if ($item === 'Username'){
                    $userlogin = $request->getParam('_username');
                    try{
                        $result = $em->getRepository('App\Entity\\'.$entityName)->findOneBy(['username'=> $userlogin]);

                    }catch (\Exception $e){
                        $e = 'Nous avons rencontré une érreur lors du chargement de l\'entité '.$entityName;
                        $checkUser = false;
                        return $checkUser;
                        // throw new \Exception($e);
                    }
                    if ($result !== null){
                        // Vérifions si le mot de passe est correct
                        if (password_verify($mdp, $result->getPassword()) === true and $result->getEnable() === true ) {
                            $result->setLastLogin(new \DateTime());
                            $em->persist($result);
                            $em->flush();
                            $checkUser = true;
                        } else {
                            $checkUser = false;
                        }
                    }else{
                        return $checkUser;
                    }
                }
    
                if ($item === 'Email'){
                    $userlogin = $request->getParam('_email');
                    try{
                        $result = $em->getRepository('App\Entity\\'.$entityName)->findOneBy(['email'=> $userlogin]);
                    }catch (\Exception $e){
                        $e = 'Nous avons rencontré une érreur lors du chargement de l\'entité' .$entityName;
                        // throw new \Exception($e);
                        $checkUser = false;
                        return $checkUser;
                    }
                    if ($result !== null){
                        // Vérifions si le mot de passe est correct
                        if (password_verify($mdp, $result->getPassword()) === true and $result->getEnable() === true ) {
                            $result->setLastLogin(new \DateTime());
                            $em->persist($result);
                            $em->flush();
                            $checkUser = true;
                        } else {
                            $checkUser = false;
                        }
                    }else{
                        return $checkUser;
                    }
                }
            }else{
            $msg =('Erreur lors de l\'authentification de l\'utilisateur... Préciser le systeme d\'authentification dans le fichier security! ');
                throw new \Exception($msg);
            }
        }else{
            $checkUser = false;
        }
        
        return $checkUser;
    }

    /**
     * @return mixed
     */
    public function getEntityName(){
        // Récupération des paramètres de sécurités
        $security = $this->container['security'];
        return $security['EntityName'];
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