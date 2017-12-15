<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixtureInterface;

/**
 * Loader
 */
interface FixtureLoadableInterface
{
    /**
     * @param string $table
     * @param FixtureInterface $fixtureSource
     * @param int|null $baseIndex
     * @return array
     */
    public function load($table, FixtureInterface $fixtureSource, $baseIndex = null);
}
