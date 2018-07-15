<?php
declare(strict_types=1);
namespace Furified\Web\FilterRules;

use ParagonIE\Ionizer\Contract\FilterContainerInterface;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class VoidFilter
 * @package Furified\Web\FilterRules
 */
class VoidFilter extends InputFilterContainer implements FilterContainerInterface
{
    /**
     * VoidFilter constructor.
     *
     * NOP. We don't have any input filters here.
     */
    public function __construct()
    {
    }
}
