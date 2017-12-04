<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Generator\GeneratorInterface;

interface FixtureInterface
{
    /**
     * @param GeneratorInterface $prototype
     * @param int|null $baseIndex
     * @return array
     */
    public function generateRecords(GeneratorInterface $prototype, $baseIndex = null);
}
