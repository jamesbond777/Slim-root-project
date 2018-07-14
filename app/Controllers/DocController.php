<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DocController extends BaseController  {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function documentation(RequestInterface $request, ResponseInterface $response)
    {
        return $this->render($response, 'doc/documentation.twig');
    }

}