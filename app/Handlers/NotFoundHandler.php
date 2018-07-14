<?php
namespace App\Handlers;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundHandler
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
    function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        // TODO: Implement __invoke() method.
        return $this->container->view->render($response, 'errors/404.twig',[
        ]);
    }
}