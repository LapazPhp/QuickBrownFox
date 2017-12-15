<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

/**
 * Generator creates a property set for single record by sequential index number.
 */
interface GeneratorInterface
{
    /**
     * @param int $index
     * @return ValueProviderInterface[]
     */
    public function extractAt($index);

    /**
     * @param int $index
     * @return array
     */
    public function generateAt($index);
}
