<?php
declare(strict_types=1);
namespace Furified\Web\Engine;

use FastRoute\Dispatcher;
use Furified\Web\Engine\Contract\MiddlewareInterface;
use Furified\Web\Engine\Exceptions\{
    FurifiedException,
    RoutingException
};
use Furified\Web\Engine\Contract\RequestHandlerInterface;
use function GuzzleHttp\Psr7\stream_for;
use ParagonIE\Ionizer\InvalidDataException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};

/**
 * Class Router
 * @package Furified\Web\Engine
 */
class Router
{
    /** @var Dispatcher $dispatcher */
    private $dispatcher;

    /**
     * Router constructor.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return array
     *
     * @todo Integrate with Anti-CSRF, CSP-Builder, etc.
     */
    public function getResponseHeaders(): array
    {
        return [];
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws RoutingException
     */
    public function getResponse(RequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        if ($routeInfo[0] !== Dispatcher::FOUND) {
            throw new RoutingException('HTTP/2 404 File Not Found', 404);
        }

        /**
         * @var string $handlerClass
         * @var array<int, string|int> $vars
         */
        $handlerClass = $routeInfo[1];
        $vars = $routeInfo[2];

        if (\is_object($handlerClass)) {
            if (!($handlerClass instanceof RequestHandlerInterface)) {
                throw new \TypeError(
                    'Handler is not an instance of RequestHandlerInterface'
                );
            }
            $handler = $handlerClass;
        } else {
            $handler = new $handlerClass($request, ...$vars);
        }

        if (!($handler instanceof RequestHandlerInterface)) {
            throw new \TypeError(
                'Handler is not an instance of RequestHandlerInterface'
            );
        }
        $request = $this->filterInput($request, $handler);
        foreach ($handler->getMiddleware() as $mw) {
            if ($mw instanceof MiddlewareInterface) {
                $request = $mw($request);
            }
        }
        return $handler($request);
    }

    /**
     * @param RequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return RequestInterface
     */
    public function filterInput(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): RequestInterface {
        $requestBody = (string) $request->getBody();
        if (empty($requestBody)) {
            return $request->withBody(stream_for(''));
        }
        $post = [];
        \parse_str($requestBody, $post);

        try {
            $fc = $handler->getInputFilterContainer();

            return $request->withBody(
                stream_for(
                    \http_build_query(
                        $fc($post)
                    )
                )
            );
        } catch (InvalidDataException $ex) {
            return $request->withBody(stream_for(''));
        }
    }

    /**
     * @param RequestInterface $request
     *
     * @return void
     *
     * @throws FurifiedException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function serve(RequestInterface $request)
    {
        try {
            $response = $this->getResponse($request);
        } catch (RoutingException $ex) {
            $response = GlobalConfig::instance()
                ->getTemplates()
                ->render('error404.twig');
        }

        $this->output($response);
    }

    /**
     * @param ResponseInterface $response
     * @return void
     */
    public function output(ResponseInterface $response)
    {
        // Standard response headers:
        foreach ($response->getHeaders() as $key => $headers) {
            foreach ($headers as $value) {
                \header($key . ': ' . $value, false);
            }
        }

        // Handler-defined response headers:
        foreach ($this->getResponseHeaders() as $key => $headers) {
            foreach ($headers as $value) {
                \header($key . ': ' . $value, false);
            }
        }
        echo (string) $response->getBody();
        exit(0);
    }
}
