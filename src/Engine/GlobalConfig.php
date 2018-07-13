<?php
declare(strict_types=1);
namespace Furified\Web\Engine;

use function FastRoute\cachedDispatcher;
use Furified\Web\Engine\Contract\CryptographicKeyInterface;
use Furified\Web\Engine\Cryptography\Key\AsymmetricPublicKey;
use Furified\Web\Engine\Cryptography\Key\AsymmetricSecretKey;
use Furified\Web\Engine\Cryptography\Key\SymmetricKey;
use Furified\Web\Engine\Exceptions\CryptoException;
use Furified\Web\Engine\Exceptions\FileNotFoundException;
use Furified\Web\Engine\Exceptions\FileReadException;
use Furified\Web\Engine\Exceptions\FurifiedException;
use Furified\Web\Engine\Exceptions\JSONException;
use ParagonIE\EasyDB\EasyDB;

/**
 * Class GlobalConfig
 * @package Furified\Web
 */
final class GlobalConfig
{
    const DEFAULT_TWIG_HOSTNAME = 'furified.com';

    /** @var string $configDir */
    private $configDir = '';

    /** @var EasyDB $db */
    private $db;

    /** @var array<string, CryptographicKeyInterface> $keyring */
    private $keyring = [];

    /** @var array $settings */
    private $settings;

    /**
     * @var GlobalConfig $instance
     */
    private static $instance;

    /**
     * GlobalConfig constructor.
     *
     * @throws FileNotFoundException
     * @throws FileReadException
     * @throws JSONException
     */
    private function __construct()
    {
        $path = FURIFIED_ROOT . '/data';
        if (\is_dir($path . '/local')) {
            // Use the local configuration if it exists.
            $path .= '/local';
        }

        $this->configDir = $path;
        $this->settings = Utility::getJsonFile($path . '/settings.json');
    }

    /**
     * @param bool $forceLoad
     *
     * @return array<string, CryptographicKeyInterface>
     * @throws CryptoException
     * @throws FileNotFoundException
     */
    private function getKeyring(bool $forceLoad = false): array
    {
        if (!empty($this->keyring) && !$forceLoad) {
            return $this->keyring;
        }
        if (!\is_readable($this->configDir . '/keys.php')) {
            throw new FileNotFoundException(
                'Cannot open ' . $this->configDir . '/keys.php'
            );
        }

        $keyring = include $this->configDir . '/keys.php';
        if (!isset($keyring['secret-key'], $keyring['public-key'], $keyring['shared-key'])) {
            throw new CryptoException('Mandatory keys are not defined in keyring');
        }
        foreach ($keyring as $key) {
            if (!($key instanceof CryptographicKeyInterface)) {
                throw new \TypeError();
            }
        }

        if (!($keyring['secret-key'] instanceof AsymmetricSecretKey)) {
            throw new \TypeError();
        }
        if (!($keyring['public-key'] instanceof AsymmetricPublicKey)) {
            throw new \TypeError();
        }
        if (!($keyring['shared-key'] instanceof SymmetricKey)) {
            throw new \TypeError();
        }
        $this->keyring = $keyring;
        return $this->keyring;
    }

    /**
     * @return AsymmetricPublicKey
     * @throws CryptoException
     * @throws FileNotFoundException
     */
    public function getPublicKey(): AsymmetricPublicKey
    {
        $keyring = $this->getKeyring();
        /** @var AsymmetricPublicKey $publicKey */
        $publicKey = $keyring['public-key'];
        if (!($keyring['public-key'] instanceof AsymmetricPublicKey)) {
            throw new \TypeError();
        }
        return $publicKey;
    }

    /**
     * @return AsymmetricSecretKey
     * @throws CryptoException
     * @throws FileNotFoundException
     */
    public function getSecretKey(): AsymmetricSecretKey
    {
        $keyring = $this->getKeyring();
        /** @var AsymmetricSecretKey $secretKey */
        $secretKey = $keyring['secret-key'];
        if (!($keyring['secret-key'] instanceof AsymmetricSecretKey)) {
            throw new \TypeError();
        }
        return $secretKey;
    }

    /**
     * @return SymmetricKey
     * @throws CryptoException
     * @throws FileNotFoundException
     */
    public function getSymmetricKey(): SymmetricKey
    {
        $keyring = $this->getKeyring();
        /** @var SymmetricKey $sharedKey */
        $sharedKey = $keyring['shared-key'];
        if (!($keyring['shared-key'] instanceof SymmetricKey)) {
            throw new \TypeError();
        }
        return $sharedKey;
    }

    /**
     * @return self
     * @throws FurifiedException
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getConfigDirectory(): string
    {
        return $this->configDir;
    }

    /**
     * @return EasyDB
     */
    public function getDatabase(): EasyDB
    {
        return $this->db;
    }

    /**
     * @return string
     */
    public function getHttpVersion(): string
    {
        return '1.1';
    }

    /**
     * @return Router
     * @throws FileReadException
     */
    public function getRouter(): Router
    {
        $routes = require_once $this->configDir . '/routes.php';
        if (!\is_callable($routes)) {
            throw new FileReadException('Cannot read routes.php');
        }
        if (!\array_key_exists('twig-cache', $this->settings)) {
            $cacheDisabled = true;
        } else {
            $cacheDisabled = !$this->settings['cache'];
        }

        $dispatcher = cachedDispatcher(
            $routes,
            [
                'cacheFile' => $this->configDir . '/route.cache',
                'cacheDisabled' => $cacheDisabled,
            ]
        );
        return new Router($dispatcher);
    }

    /**
     * @param string $subdir
     * @return \Twig_Environment
     */
    public function getTwig(
        string $subdir = self::DEFAULT_TWIG_HOSTNAME
    ): \Twig_Environment {
        $twig_loader = new \Twig_Loader_Filesystem([
            FURIFIED_ROOT . '/templates/' . $subdir,
            FURIFIED_ROOT . '/templates/common'
        ]);
        $twig_env = new \Twig_Environment($twig_loader);

        /** @var array<string, array<string, callable>> $filters */
        $custom = require $this->configDir . '/twig.php';

        foreach ($custom['functions'] as $name => $callable) {
            $twig_env->addFunction(new \Twig_Function($name, $callable));
        }
        foreach ($custom['filters'] as $name => $callable) {
            $twig_env->addFilter(new \Twig_Filter($name, $callable));
        }

        return $twig_env;
    }

    /**
     * Get a TemplateRenderer for a specific namespace (i.e. subdirectory
     * of the templates folder)
     *
     * @param string $subdir
     * @return TemplateRenderer
     */
    public function getTemplates(
        string $subdir = self::DEFAULT_TWIG_HOSTNAME
    ): TemplateRenderer {
        return new TemplateRenderer($this->getTwig($subdir));
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        if (isset($this->settings['debug'])) {
            return true;
        }
        return false;
    }
}
