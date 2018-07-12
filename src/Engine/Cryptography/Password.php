<?php
declare(strict_types=1);
namespace Furified\Web\Engine\Cryptography;

use Furified\Web\Engine\Cryptography\Key\SymmetricKey;
use Furified\Web\Engine\Exceptions\CryptoException;
use ParagonIE\HiddenString\HiddenString;

/**
 * Class Password
 * @package Furified\Web\Engine\Cryptography
 */
final class Password
{
    const DEFAULT = [
        'mem' => SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
        'ops' => SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE
    ];

    /** @var SymmetricKey $key */
    private $key;

    /** @var array<string, int> $options */
    private $options;

    /**
     * Password constructor.
     *
     * @param SymmetricKey $key
     * @param array $options
     */
    public function __construct(
        SymmetricKey $key,
        array $options = self::DEFAULT
    ) {
        $this->key = $key;
        $this->options = $options;
    }

    /**
     * @param HiddenString $password
     *
     * @return string
     * @throws \SodiumException
     */
    public function hash(HiddenString $password): string
    {
        $hash = \sodium_crypto_pwhash_str(
            $password->getString(),
            $this->options['ops'],
            $this->options['mem']
        );
        $ciphertext = Symmetric::encrypt(
            new HiddenString($hash),
            $this->key
        );
        \sodium_memzero($hash);
        return $ciphertext;
    }

    /**
     * @param HiddenString $password
     * @param string $encryptedHash
     *
     * @return bool
     * @throws CryptoException
     * @throws \SodiumException
     */
    public function verify(HiddenString $password, string $encryptedHash): bool
    {
        $hash = Symmetric::decrypt($encryptedHash, $this->key);
        return \sodium_crypto_pwhash_str_verify(
            $password->getString(),
            $hash->getString()
        );
    }
}
