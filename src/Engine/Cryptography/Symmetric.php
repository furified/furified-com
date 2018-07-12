<?php
declare(strict_types=1);
namespace Furified\Web\Engine\Cryptography;

use Furified\Web\Engine\Cryptography\Key\SymmetricKey;
use Furified\Web\Engine\Exceptions\CryptoException;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\Binary;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE_Sodium_Compat as NaCl;

/**
 * Class Symmetric
 * @package Furified\Web\Engine\Cryptography
 */
abstract class Symmetric
{
    /*
     * The last three digits represent version information, although no change
     * is anticipated at this time.
     */
    const HEADER = "furry100";

    /*
     * If we need to change protocol versions, we'll greenlight the new version
     * headers in this hard-coded constant:
     */
    const ALLOWED_HEADERS = ["furry100"];

    /**
     * @param HiddenString $message
     * @param SymmetricKey $key
     *
     * @return string
     * @throws \SodiumException
     */
    public static function encrypt(
        HiddenString $message,
        SymmetricKey $key
    ): string {
        $nonce = \random_bytes(24);

        // This is IND-CCA3 secure:
        $ciphertext = NaCl::crypto_aead_xchacha20poly1305_ietf_encrypt(
            $message->getString(),
            self::HEADER . $nonce,
            $nonce,
            $key->getRawKeyMaterial()
        );
        return self::HEADER . Base64UrlSafe::encode($nonce . $ciphertext);
    }

    /**
     * @param string $encrypted
     * @param SymmetricKey $key
     *
     * @return HiddenString
     * @throws CryptoException
     * @throws \SodiumException
     */
    public static function decrypt(
        string $encrypted,
        SymmetricKey $key
    ): HiddenString {
        if (Binary::safeStrlen($encrypted) < 8) {
            throw new CryptoException(
                'String too short to be valid ciphertext'
            );
        }
        $header = Binary::safeSubstr($encrypted, 0, 8);
        if (!\in_array($header, self::ALLOWED_HEADERS, true)) {
            throw new CryptoException('Invalid message header');
        }
        $encoded = Binary::safeSubstr($encrypted, 8);
        $decoded = Base64UrlSafe::decode($encoded);
        if (Binary::safeStrlen($decoded) < 40) {
            throw new CryptoException(
                'Decoded string too short to be valid ciphertext'
            );
        }

        $nonce = Binary::safeSubstr($decoded, 0, 24);
        $ciphertext = Binary::safeSubstr($decoded, 24);

        $plaintext = NaCl::crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            self::HEADER . $nonce,
            $nonce,
            $key->getRawKeyMaterial()
        );
        if (!\is_string($plaintext)) {
            throw new CryptoException('Invalid message body');
        }
        $hiddenString = new HiddenString($plaintext);
        \sodium_memzero($plaintext);
        return $hiddenString;
    }
}
