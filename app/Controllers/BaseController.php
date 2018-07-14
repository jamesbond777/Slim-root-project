<?php
namespace App\Controllers;

use App\Entity\Users;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\UploadedFile;

class BaseController {

    protected $container;

    /**
     * Controller constructor.
     * @param $container
     */
    public function __construct($container){
        $this->container = $container;
    }

    /**
     * @param ResponseInterface $response
     * @param $file
     * @param array $params
     * @return mixed
     */
    public function render($response, $file, $params = []){
      return  $this->container->view->render($response, $file, $params);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getContainer($name){
       return $this->container[$name];
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
     * @param ResponseInterface $response
     * @param string $route
     * @param int $status
     * @return ResponseInterface static
     */
    public function redirect(ResponseInterface $response, $route, array $args=[], $status = 302){
        return $response->withStatus($status)->withHeader('location', $this->container->router->pathFor($route, $args));
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->container->getEntityManager;
    }

    /**
     * @param string $subject
     * @param array $destinataires
     * @param string $pathTemplate
     * @param string null $setFrom
     * @param array null $attach
     * @return \Swift_Message $this
     */
    public function prepareEmailWithTemplate($subject,$destinataires,$pathTemplate, $setFrom = null, $attach = null)
    {
        // creation du message
        $message = \Swift_Message::newInstance();
        $params = $this->getContainer('parameters');
        $options = $params['swiftmailer'];
        if($setFrom === null){
            $setFrom = $options['setFrom'];
        }

        // hop, on défini le fichier du mail
        $content = file_get_contents($pathTemplate) ;

        $message->setSubject($subject);
        $message->setFrom($setFrom);
        $message->setTo($destinataires);
        $message->setBody($content,'text/html');

        // Ajout de pièces jointes depuis un dossier
        if ($attach !== null){
            foreach ($attach as $key => $value){
                //$key = pathToFile; $value = extension
                $message->attach(\Swift_Attachment::fromPath($key,'application/'.$value));
            }
        }
        return $message;
    }

    /**
     * @param string $subject
     * @param array $destinataires
     * @param string $body
     * @param string null $setFrom
     * @param array null $attach
     * @return \Swift_Message $this
     */
    public function prepareEmail($subject, $destinataires, $body, $setFrom = null, $attach = null)
    {
        // creation du message
        $message = \Swift_Message::newInstance();
        $params = $this->getContainer('parameters');
        $options = $params['swiftmailer'];
        if($setFrom === null){
            $setFrom = $options['setFrom'];
        }
        $message->setSubject($subject);
        $message->setFrom($setFrom);
        $message->setTo($destinataires);
        $message->setBody($body);

        // Ajout de pièces jointes depuis un dossier
        if ($attach !== null){
            foreach ($attach as $key => $value){
                //$key = pathToFile; $value = extension
                $message->attach(\Swift_Attachment::fromPath($key,'application/'.$value));
            }
        }
        return $message;
    }

    /**
     * @param string $directory
     * @param UploadedFile $uploadedFile
     * @return string
     */
    function moveUploadedFile($directory, UploadedFile $uploadedFile, $contraintes = [])
    {
        $flash = null;
        $imageInfo = [];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            // Vérification des extensions
            if (!in_array($extension, $contraintes)){
                $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
                $filename = sprintf('%s.%0.8s', $basename, $extension);

                $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
                $this->flash("image(s) enregistrée(s) avec succès");
                $imageInfo = [
                    'originalName' => trim($uploadedFile->getClientFilename(),'.'.$extension),
                    'filename' => $filename,
                    'extension' => $extension,
                ];
                return $imageInfo;
            }else{
                $flash = $this->flash("l'extension du fichié est invalide",'error');
            }
        }

        return $flash;
    }

    /**
     * @param $value
     * @param $type
     * @return bool
     */
    function validate($value, $type){
        switch ($type) {
            case "Email":
                $result = \Respect\Validation\Validator::email()->validate($value);
                break;
            case "Text":
                $result =  \Respect\Validation\Validator::stringType()->validate($value);
                break;
            case "url":
                $result = \Respect\Validation\Validator::url()->validate($value);
                break;
            case "number":
                $result = \Respect\Validation\Validator::intType()->validate($value);
                break;
            case "Datetime":
                $result = \Respect\Validation\Validator::date('d/m/Y h:i:s')->validate($value);
                break;
            case "Date":
                $result = \Respect\Validation\Validator::date('d/m/Y')->validate($value);
                break;
            case "Empty":
                $result = \Respect\Validation\Validator::notEmpty()->validate($value);
                break;
        }
        return $result;
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
     * @return mixed
     */
    public function getSecurityLogin(){
        // Récupération des paramètres de sécurités
        $security = $this->container['security'];
        return $security['AuthenticateWith']['login'];
    }

    /**
     * @param $request
     * @param $response
     * @return bool
     */
    public function secureRequest($request, $response){
        $access = true;
        $activUri = $request->getUri()->getPath();
        $pathSecurity = $this->container['security']['PathSecurity'];
        foreach ($pathSecurity as $path => $role){
            if (isset($_COOKIE['user_connected'])) {
                if ($_COOKIE['user_connected']['role'] !== $role and substr_count($activUri, $path) > 0){
                    $access = false;
                }
            }
        }
        if ($access){
            return true;
        }else{
            return $this->container->view->render($response, 'errors/403.twig',[
            ]);
        }
    }

    /**
     * @return mixed
     */
    public function getUploadDir(){
        return $this->getContainer("upload_directory");
    }

}