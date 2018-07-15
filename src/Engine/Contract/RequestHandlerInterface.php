<?php
namespace Furified\Web\Engine\Contract;

use ParagonIE\Ionizer\Contract\FilterContainerInterface;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};

/**
 * Interface RequestHandlerInterface
 * @package Furified\Web\Contract
 */
interface RequestHandlerInterface
{
    /**
     * @return FilterContainerInterface
     */
    public function getInputFilterContainer(): FilterContainerInterface;

    /**
     * @return array<int, MiddlewareInterface>
     */
    public function getMiddleware(): array;

    /**
     * Process an HTTP request, produce an HTTP response
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request): ResponseInterface;
}
