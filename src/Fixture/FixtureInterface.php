<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Generator\GeneratorInterface;

/**
 * Fixture is iterable record set which is ready to be loaded into target table.
 */
interface FixtureInterface
{
    /**
     * @param GeneratorInterface $prototype
     * @param int|null $baseIndex
     * @return array
     */
    public function generateRecords(GeneratorInterface $prototype, $baseIndex = null);
}
