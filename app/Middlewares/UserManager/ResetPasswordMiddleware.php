<?php
namespace Middlewares\UserManager;

use Respect\Validation\Validator;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class ResetPasswordMiddleware {

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
        $result = $this->resetPass($request, $response);
        if(empty($result['errors'])){
            $_SESSION['success'] = $result;
        }else{
            $_SESSION['success'] = null;
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
    public function resetPass(Request $request, Response $response){
        // Récupération de l'entity manager de doctrine
        $em = $this->container['getEntityManager'];
        $errors = [];
        Validator::notEmpty()->validate($request->getParam('_email')) || $errors['_email'] = 'Veuillez saisir un mot de passe correcte';

        $result = [
            'user' => null,
            'errors' => $errors
        ];
        if (empty($errors)){
         if($this->checkBddConnect()){
            //vérifions que l'utilisateur existe  déjà
            $user = $em->getRepository('App\Entity\Users')->findOneBy(['email' => $request->getParam('_email')]);
                if ($user !== null){
                    if ($this->getTokenValidityTime() !== '' or $this->getTokenValidityTime() !== 0){
                        $token = hash('sha1', $request->getParam('email').' reset password');
                        $user->setToken($token);
                        $now = new \DateTime();
                        $now_tms = $now->getTimestamp();
                        $openTime = $this->getTokenValidityTime();
                        $endTime = $now_tms + $openTime;
                        $endTime = date('d-m-Y H:i:s', $endTime);
                        // for example: you have a string with the following
                        $dateFormat = 'd-m-Y H:i:s';

                        $dateTime = \DateTime::createFromFormat($dateFormat, $endTime);
                        $user->setPasswordRequestedDeadTime($dateTime);
                        $em->persist($user);
                        $em->flush();
                        $result['user'] = $user;
                    }else{
                        $errors =['tokenTime' => "Veuillez configurer la durée de validité de la requette..." ];
                        return $result = [
                            'user' => null,
                            'errors' => $errors
                        ];
                    }
                }else{
                    $errors['email'] = "Email inconnue... veuillez saisir un email valide.";
                    $result = [
                        'user' => null,
                        'errors' => $errors
                    ];
                }
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