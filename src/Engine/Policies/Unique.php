<?php
declare(strict_types=1);
namespace Furified\Web\Engine\Policies;

/**
 * Interface Unique
 * @package Furified\Web\Engine\Policies
 */
interface Unique
{
    /**
     * @param int $id
     * @return string
     * @throws \Error
     */
    public function getCacheKey(int $id = 0): string;
}
