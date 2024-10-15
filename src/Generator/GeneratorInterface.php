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
     * @return list<ValueProviderInterface>
     */
    public function extractAt(int $index): array;

    /**
     * @param int $index
     * @return array<string,mixed>
     */
    public function generateAt(int $index): array;
}
