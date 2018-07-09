<?php
declare(strict_types=1);
namespace Furified\Web\Engine;

use Furified\Web\Engine\Exceptions\{
    FileNotFoundException,
    FileReadException,
    JSONException
};

/**
 * Class Utility
 * @package Furified\Web\Engine
 */
abstract class Utility
{
    /**
     * Load a JSON file's contents, return an array.
     *
     * @param string $path
     * @return array
     *
     * @throws FileNotFoundException
     * @throws FileReadException
     * @throws JSONException
     */
    public static function getJsonFile(string $path): array
    {
        if (!\is_readable($path)) {
            throw new FileNotFoundException(
                'Configuration file not found: ' . $path
            );
        }
        /** @var string|bool $raw */
        $raw = \file_get_contents($path);
        if (!\is_string($raw)) {
            throw new FileReadException(
                'Could not read configuration file: ' . $path
            );
        }

        /** @var array|null $decoded */
        $decoded = \json_decode($raw, true);
        if (!\is_array($decoded)) {
            throw new JSONException(
                \json_last_error_msg(),
                \json_last_error()
            );
        }
        return $decoded;
    }

    /**
     * @param string $class
     * @return string
     */
    public static function decorateClassName($class = ''): string
    {
        return 'Object (' . \trim($class, '\\') . ')';
    }

    /**
     * Get a variable's type. If it's an object, also get the class name.
     *
     * @param mixed $mixed
     * @return string
     */
    public static function getGenericType($mixed = null): string
    {
        if (\func_num_args() === 0) {
            return 'void';
        }
        if ($mixed === null) {
            return 'null';
        }
        if (\is_object($mixed)) {
            return static::decorateClassName(\get_class($mixed));
        }
        $type = \gettype($mixed);
        switch ($type) {
            case 'boolean':
                return 'bool';
            case 'double':
                return 'float';
            case 'integer':
                return 'int';
            default:
                return $type;
        }
    }
}
