<?php
namespace App\Controllers;

use App\Entity\Users;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class AuthentificationController extends BaseController  {

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function login(Request $request, ResponseInterface $response)
    {
        return $this->render($response, 'Authentification/login.twig',[
        ]);
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function loginCheck(Request $request, ResponseInterface $response)
    {
        if ($_SESSION['userExist'] === true){
            //Chargement de l'entity manager et des parametres de securité
            $em = $this->getEntityManager();
            $security = $this->getContainer('security');
            $pathSecurity = $security['PathSecurity'];
            $configLogin = strtolower($this->getSecurityLogin());
            //Récupération du champ rempli par l'utilisateur
            $login = $request->getParam('_'.$configLogin);
            $user = $em->getRepository('App\Entity\Users')->findOneBy([$configLogin => $login]);
            //Récupération de la route correspondant à son ROLE
            $route = $this->getPathRedirect($user, $pathSecurity, $request);
            if ($route !== 'login'){
               $this->welcomeMessage($user);
                return  $this->redirect($response, $route);
            }else{
                return  $this->redirect($response, $route);
            }
        }else {
            $this->flash('Utilisateur inconnue','error');
            return  $this->redirect($response, 'login');
        }

    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function logout(RequestInterface $request, ResponseInterface $response)
    {
        return  $this->redirect($response, 'login');
    }


    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function register(Request $request, ResponseInterface $response)
    {
        return $this->render($response, 'Authentification/register.twig',[
        ]);
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function registrationCheck(Request $request, ResponseInterface $response)
    {
        if (count($_SESSION['success']['errors']) === 0){
            $user = $_SESSION['success']['user'];

            if($user != null){
                //Vérifions si les email de confirmations sont actifs
                if ($this->getContainer('security')['EmailConfirmation'] === true){
                    //Vérifions si nous sommes en environnement de Dev ou de prod
                    if ($this->getContainer("parameters")['env'] == "dev"){
                        $path = $request->getUri()->getHost().":8080/registration/token/";
                    }else{
                        $path = $request->getUri()->getHost()."/registration/token/";
                    }
                    $msg = $this->prepareEmailConfirmation($user, $path);
                    $mailer = $this->getContainer('mailer');
                    $mailer->send($msg);
                    return $this->render($response, 'Authentification/confirmation.twig',[
                    ]);
                }else{
                    $em = $this->container['getEntityManager'];
                    $user->setEnable(1);
                    $user->setToken("");
                    $em->persist($user);
                    $em->flush();
                    $this->flash('Félicitation votre compte est désormais activé! vous pouvez vous connecter');
                    return  $this->redirect($response, 'login');
                }
            }else{
                $this->flash("Oops... veuillez vérifier votre configuration, car nous avons eu un problème avec votre base de donnée et vos entités.", 'error');
                return  $this->redirect($response, 'register');
            }
        }else{
            $this->flash(' Certains champs n\'ont pas été rempli correctement ', 'error');
            $this->flash($_SESSION['success']['errors'], 'error');
            return  $this->redirect($response, 'register');
        }
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function confirmUser(Request $request, ResponseInterface $response, $args)
    {
        $tokenId = $args['id'];
        $em = $this->getEntityManager();
        $user = $em->getRepository('App\Entity\Users')->findOneBy([],['id'=>'DESC']);
        if ($user->getToken() === $tokenId){
            $user->setEnable(1);
            $user->setToken(NULL);
            $em->persist($user);
            $em->flush();
            $this->flash('Félicitation votre compte est désormais activé! vous pouvez vous connecter');
            return  $this->redirect($response, 'login');
        }else{
            return $this->render($response, 'errors/404.twig',[
            ]);
        }
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function forgotPassword(RequestInterface $request, ResponseInterface $response)
    {
        return $this->render($response, 'Authentification/forgotPass.twig',[
        ]);
    }


    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function passwordReset(Request $request, ResponseInterface $response)
    {
        if (count($_SESSION['success']['errors']) === 0){
            $user = $_SESSION['success']['user'];
            //Envoi de mail contenant la clé générée
            //Vérifions si nous sommes en environnement de Dev ou de prod
            var_dump($this->getContainer("parameters")['env']);
            if ($this->getContainer("parameters")['env'] == "dev"){
                $path = $request->getUri()->getHost().":8080/reset_password/";
//                var_dump($path);
//                die();
            }else{
                $path = $request->getUri()->getHost()."/reset_password/";
            }
                if($user != null){
                    $msg = $this->preparePassConfirmation($user, $path);
                    $mailer = $this->getContainer('mailer');
                    $mailer->send($msg);
                    return $this->render($response, 'Authentification/confirmation-pass.twig',array());
                }else{
                    $this->flash("Oops... veuillez vérifier votre configuration, car nous avons eu un problème avec la base de donnée et l'entité utilisateur.", 'error');
                    return  $this->redirect($response, 'forgot_password');
                }
        }else{
            $this->flash($_SESSION['success']['errors'], 'error');
            return  $this->redirect($response, 'forgot_password');
        }
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function confirmPass(Request $request, ResponseInterface $response, $args)
    {
        $tokenId = $args['token'];
        $em = $this->getEntityManager();
        $user = $em->getRepository('App\Entity\Users')->findOneBy(['token'=> $tokenId]);
        if ($user !== null){
            $deadTime = $user->getPasswordRequestedDeadTime();
            $deadTime_tms = $deadTime->getTimestamp();
            $now_tms = strtotime(date('d-m-Y H:i:s'));
            if ($deadTime_tms >= $now_tms){
                $this->flash('Félicitation vous pouvez modifier votre mot de passe');
                return  $this->redirect($response, 'update_Pass', array(
                    'id' => $user->getId(),
                    'token' => $tokenId
                ));
            }else{
                $this->flash('token expire... Please restart the operation','error');
                return  $this->redirect($response, 'forgot_password');
            }
        }else{
            return $this->render($response, 'errors/404.twig', array(
            ));
        }
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function UpdatePass(RequestInterface $request, ResponseInterface $response, $args)
    {
        return $this->render($response, 'Authentification/updatePass.twig',[
            'userId' => $args['id'],
            'token' => $args['token']
        ]);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function changePassword(RequestInterface $request, ResponseInterface $response, $args)
    {
        if (count($_SESSION['success']['errors']) === 0){
                $userId = $args['id'];
                // Récupération de l'entity manager de doctrine
                $em = $this->container['getEntityManager'];
                $user = $em->getRepository('App\Entity\Users')->findOneBy(['id' => $userId]);
                if ($user !== null){
                    $user->setToken(NULL);
                    $user->setPasswordRequestedDeadTime(NULL);
                    $user->setPassword($request->getParam('_password_confirmation'));
                    $em->persist($user);
                    $em->flush();
                    $this->flash('Félicitation votre mot de passe à été modifier avec succès! Vous pouvez vous connecter.');
                    return  $this->redirect($response, 'login');
                }else{
                    $errors['user'] = "Utilisateur inconnue...";
                    $result = ['errors' => $errors];
                    $this->flash($result['errors'], 'errors');
                    return  $this->redirect($response, 'forgot_password');
                }

        }else{
            $this->flash($_SESSION['success']['errors'], 'errors');
            return $this->redirect($response, 'update_Pass', array(
                'id' => $args['id'],
                'token' => $args['token']
            ));
        }
    }

    /**
     * @param Users $user
     * @param $path
     * @return \Swift_Message
     */
    public function prepareEmailConfirmation(Users $user, $path){
        $subject = "Email Confirmation";
        $body = $this->getEmailModel_1($user, $path);
        $message = \Swift_Message::newInstance();

        $params = $this->getContainer('parameters');
        $options = $params['swiftmailer'];
        $setFrom = $options['setFrom'];

        $message->setSubject($subject);
        $message->setFrom($setFrom);
        $message->setTo($user->getEmail());
        $message->setBody($body,'text/html');
       return $message;
    }

    /**
     * @param Users $user
     * @param $path
     * @return \Swift_Message
     */
    public function preparePassConfirmation(Users $user, $path){
        $subject = "Email Confirmation";
        $body = $this->getEmailModel_2($user, $path);
        $message = \Swift_Message::newInstance();

        $params = $this->getContainer('parameters');
        $options = $params['swiftmailer'];
        $setFrom = $options['setFrom'];

        $message->setSubject($subject);
        $message->setFrom($setFrom);
        $message->setTo($user->getEmail());
        $message->setBody($body,'text/html');
       return $message;
    }

    /**
     * @param $user
     * @param $path
     * @return string
     */
    public function getEmailModel_1($user,$path){
        return "
         <table class=\"main\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
        <tr>
            <td class=\"content-wrap\">
                <table  cellpadding=\"0\" cellspacing=\"0\">
                    <tr>
                        <td class=\"content-block\">
                            <h3>WELCOME ".strtoupper($user->getEmail())." IN THE SLIM APP</h3>
                        </td>
                    </tr>
                    <tr>
                        <td class=\"content-block\">
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem
                            Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                            unknown printer took a galley of type and scrambled it to make a type specimen
                            book. It has survived not only five centuries.
                        </td>
                    </tr>
                    <tr>
                        <td class=\"content-block\">
                        Thank you for your registration ! Please click to confirm your registration.
                        </td>
                    </tr>
                    <tr>
                        <td class=\"content-block aligncenter\">
                            <a href=\"$path".$user->getToken()."\" class=\"btn-primary\">Confirm email address</a>
                        </td>
                    </tr>
                  </table>
            </td>
        </tr>
    </table>
         ";
    }

    /**
     * @param $user
     * @param $path
     * @return string
     */
    public function getEmailModel_2($user,$path){
        return "
         <table class=\"main\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
        <tr>
            <td class=\"content-wrap\">
                <table  cellpadding=\"0\" cellspacing=\"0\">
                    <tr>
                        <td class=\"content-block\">
                            <h3>HI ".strtoupper($user->getUsername())."</h3>
                        </td>
                    </tr>
                    <tr>
                        <td class=\"content-block\">
                            Did you want really change your password? If No ignore this mail.
                        </td>
                    </tr>
                    <tr>
                        <td class=\"content-block\">
                        Thank you for your using slim app.
                        </td>
                    </tr>
                    <tr>
                        <td class=\"content-block aligncenter\">
                            <a href=\"".$path."".$user->getToken()."\" class=\"btn-primary\">Confirm your request</a>
                        </td>
                    </tr>
                  </table>
            </td>
        </tr>
    </table>
         ";
    }

    /**
     * @param $user
     * @return mixed
     */
    public function welcomeMessage($user){
        if ($user->getRole() === 'ROLE_ADMIN'){
          return  $this->flash('Bienvenu admin');
        }elseif ($user->getRole() === 'ROLE_USER'){
          return $this->flash('Bienvenu user');
        }elseif ($user->getRole() === 'ROLE_SUPER_ADMIN'){
          return  $this->flash('Bienvenu super admin');
        }
    }
}