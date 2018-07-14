<?php
namespace Middlewares\UserManager;

use App\Middlewares\BaseMiddleware;
use Slim\Http\Request;
use Slim\Http\Response;

class LogoutMiddleware extends BaseMiddleware {


    public function __invoke(Request $request, Response $response, $next)
    {
        $this->logout();
        $response = $next($request, $response);
        $em = $this->container['getEntityManager'];
        $entityName = $this->getEntityName();
        $result = null;
        if ($this->checkBddConnect()){
            try{
                $result = $em->getRepository('App\Entity\\'.$entityName)->findAll();
            }catch (\Exception $e){
                $e = 'Oops! Nous avons rencontré une érreur lors du chargement de l\'entité... Veuillez vérifier vos configurations';
                $this->flash($e, "error");
            }
            if ($result !== null){
            $this->flash("Vous avez été déconnecté !", "error");
            }
        }else{
            $this->flash("Base de donnée inexistante... veuillez créer une base de donnée", "error");
        }
        return $response;
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