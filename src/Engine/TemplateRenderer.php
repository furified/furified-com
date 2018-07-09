<?php
namespace Furified\Web\Engine;

use Furified\Web\Engine\Exceptions\FurifiedException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TemplateRenderer
 * @package Furified\Web\Engine
 */
class TemplateRenderer
{
    /** @var \Twig_Environment $env */
    private $env;

    /**
     * TemplateRenderer constructor.
     *
     * @param \Twig_Environment $env
     */
    public function __construct(\Twig_Environment $env)
    {
        $this->env = $env;
    }

    /**
     * @param string $template
     * @param array $context
     * @param int $statusCode
     * @param array $headers
     *
     * @return ResponseInterface
     *
     * @throws FurifiedException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render(
        string $template,
        array $context = [],
        int $statusCode = 200,
        array $headers = []
    ): ResponseInterface {
        return new Response(
            $statusCode,
            $headers,
            $this->env->render($template, $context),
            GlobalConfig::instance()->getHttpVersion()
        );
    }
}
