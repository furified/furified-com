<?php
namespace Furified\Web\Engine\Contract;

use Psr\Http\Message\RequestInterface;

/**
 * Interface MiddlewareInterface
 * @package Furified\Web\Contract
 */
interface MiddlewareInterface
{
    /**
     * Middleware acts on requests before the handler.
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function __invoke(RequestInterface $request): RequestInterface;
}
