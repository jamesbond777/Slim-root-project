<?php
namespace App\Handlers;
use Slim\Http\Request;
use Slim\Http\Response;

class PhpErrorHandler
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
    function __invoke(Request $request, Response $response)
    {
        return $this->container->view->render($response, 'errors/500.twig',[
        ]);
    }
}