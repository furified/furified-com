<?php
declare(strict_types=1);
namespace Furified\Web\RequestHandler;

use Furified\Web\Engine\Contract\RequestHandlerInterface;
use Furified\Web\Engine\Exceptions\FurifiedException;
use Furified\Web\Engine\GlobalConfig;
use Furified\Web\FilterRules\VoidFilter;
use ParagonIE\Ionizer\Contract\FilterContainerInterface;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};

/**
 * Class FrontPage
 * @package Furified\Web\RequestHandler
 */
class DefaultPage implements RequestHandlerInterface
{
    /**
     * @return FilterContainerInterface
     */
    public function getInputFilterContainer(): FilterContainerInterface
    {
        return new VoidFilter();
    }

    /**
     * @return array<int, MiddlewareInterface>
     */
    public function getMiddleware(): array
    {
        return [];
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws FurifiedException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function __invoke(RequestInterface $request): ResponseInterface
    {
        return GlobalConfig::instance()
           ->getTemplates('furified.com')
           ->render('index.twig');
    }
}
