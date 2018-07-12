<?php
declare(strict_types=1);
namespace Furified\Tests\Engine\Cryptography;

use Furified\Web\Engine\Cryptography\Key\SymmetricKey;
use Furified\Web\Engine\Cryptography\Symmetric;
use Furified\Web\Engine\Exceptions\CryptoException;
use ParagonIE\HiddenString\HiddenString;
use PHPUnit\Framework\TestCase;

/**
 * Class SymmetricTest
 * @package Furified\Tests\Engine\Cryptography
 */
class SymmetricTest extends TestCase
{
    /**
     * @throws CryptoException
     * @throws \SodiumException
     */
    public function testEncryptDecrypt()
    {
        $key = SymmetricKey::generate();

        $message = new HiddenString('This is a secret, okay?');

        $encrypted = Symmetric::encrypt($message, $key);
        $decrypted = Symmetric::decrypt($encrypted, $key);

        $this->assertSame(
            $message->getString(),
            $decrypted->getString(),
            'Encryption is not invertible! Or a bug in our high-level protocol.'
        );
    }
}
