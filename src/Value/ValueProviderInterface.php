<?php
namespace Lapaz\QuickBrownFox\Value;

/**
 * ValueProvider provides a value by sequential index number.
 */
interface ValueProviderInterface
{
    /**
     * @param int $index
     * @return mixed
     */
    public function getAt(int $index): mixed;
}
