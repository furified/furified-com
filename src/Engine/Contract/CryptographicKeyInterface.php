<?php
declare(strict_types=1);
namespace Furified\Web\Engine\Contract;

use ParagonIE\HiddenString\HiddenString;

/**
 * Interface CryptographicKeyInterface
 * @package Furified\Web\Engine\Contract
 */
interface CryptographicKeyInterface
{
    /**
     * @return HiddenString
     */
    public function getHiddenString(): HiddenString;

    /**
     * Hazardous Material: Don't use this method recklessly.
     *
     * @return string
     */
    public function getRawKeyMaterial(): string;
}
