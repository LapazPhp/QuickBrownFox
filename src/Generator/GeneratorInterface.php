<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

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
