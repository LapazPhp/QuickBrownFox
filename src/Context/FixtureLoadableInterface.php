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
     * @return list<int|string>
     */
    public function load(string $table, FixtureInterface $fixtureSource, ?int $baseIndex = null): array;
}
