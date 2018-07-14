<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class PagesController extends BaseController  {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function home(RequestInterface $request, ResponseInterface $response)
    {
       $base_dir = 'App\Controlleurs\PageControlleur';
       return $this->render($response, 'default/index.twig',[
           'base_dir' => $base_dir,
           'exist' => $_SESSION['userExist'],
       ]);
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function admin(Request $request, ResponseInterface $response)
    {
      $result = $this->secureRequest($request,$response);
      if ($result === true){
          return $this->render($response, 'default/admin.twig',[
          ]);
      }else{
          return $result;
      }
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function user(Request $request, ResponseInterface $response)
    {
      $result = $this->secureRequest($request,$response);
      if ($result === true){
          return $this->render($response, 'default/admin.twig',[]);
      }else{
          return $result;
      }
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     *
     */
    public function test(RequestInterface $request, ResponseInterface $response)
    {

        return $this->render($response, 'default/test.twig',array(

        ));
    }

//    public function newImage(Request $request, Response $response)
//    {
//        $directory = $this->getUploadDir();
//        $uploadedFiles = $request->getUploadedFiles();
//
//        $extensions = ['docx','pdf','JSON'];
//        // handle single input with single file upload
//        $example1 = $uploadedFiles['example1'];
//        $filename = $this->moveUploadedFile($directory, $example1, $extensions);
//
//        $example2 = $uploadedFiles['example2'];
//        foreach ($example2 as $uploadedFile) {
//            $filename = $this->moveUploadedFile($directory, $uploadedFile, $extensions);
//        }
//
//        $example3 = $uploadedFiles['example3'];
//        foreach ($example3 as $uploadedFile) {
//            $filename = $this->moveUploadedFile($directory, $uploadedFile, $extensions);
//        }
//
//        return $this->redirect($response, 'test');
//    }
}