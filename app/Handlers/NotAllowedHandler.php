<?php
namespace App\Handlers;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use HttpRequestMethodException;

class NotAllowedHandler
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
    function __invoke(RequestInterface $request, ResponseInterface $response, HttpRequestMethodException $methods)
    {
        // TODO: Implement __invoke() method.
        return $this->container['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html')
            ->write('Method must be one of: ' . implode(', ', $methods));

    }
}