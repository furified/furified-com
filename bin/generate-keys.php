<?php
declare(strict_types=1);
use Furified\Web\Engine\GlobalConfig;

require '_bin_autoload.php';
/** @var GlobalConfig $config */

$dir = $config->getConfigDirectory();
if (\file_exists($dir . '/keys.php')) {
    echo 'File ' . $dir . '/keys.php already exists.', PHP_EOL;
    exit(1);
}

$keypair = sodium_crypto_sign_keypair();
$sk      = sodium_crypto_sign_secretkey($keypair);
$pk      = sodium_crypto_sign_publickey($keypair);
$shared  = sodium_crypto_secretbox_keygen();

file_put_contents(
    $dir . '/keys.php',
    '<?php' . PHP_EOL .
        'use Furified\Web\Engine\Cryptography\Key\{' . PHP_EOL .
        '    AsymmetricPublicKey,' . PHP_EOL .
        '    AsymmetricSecretKey,' . PHP_EOL .
        '    SymmetricKey' . PHP_EOL .
        '};' . PHP_EOL .
        'use ParagonIE\ConstantTime\Hex;' . PHP_EOL .
        'use ParagonIE\HiddenString\HiddenString;' . PHP_EOL .
        PHP_EOL .
        '/* Generated by ../bin/generate-keys.php */ ' . PHP_EOL .
        PHP_EOL .
        'return [' . PHP_EOL .
        '    "secret-key" => new AsymmetricSecretKey(' . PHP_EOL .
        '        new HiddenString(' . PHP_EOL .
        '            Hex::decode("' . sodium_bin2hex($sk) . '")' . PHP_EOL .
        '        )' . PHP_EOL .
        '    ),' . PHP_EOL .
        '    "public-key" => new AsymmetricPublicKey(' . PHP_EOL .
        '        new HiddenString(' . PHP_EOL .
        '            Hex::decode("' . sodium_bin2hex($pk) . '")' . PHP_EOL .
        '        )' . PHP_EOL .
        '    ),' . PHP_EOL .
        '    "shared-key" => new SymmetricKey(' . PHP_EOL .
        '        new HiddenString(' . PHP_EOL .
        '            Hex::decode("' . sodium_bin2hex($shared) . '")' . PHP_EOL .
        '        )' . PHP_EOL .
        '    )' . PHP_EOL .
        '];' . PHP_EOL
);

sodium_memzero($keypair);
sodium_memzero($sk);
sodium_memzero($pk);
sodium_memzero($shared);
