<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;

class GeneratorSupportedFixture implements FixtureInterface
{
    /**
     * @param FixtureInterface $baseFixture
     * @param GeneratorInterface $generator
     */
    public function __construct(
        protected FixtureInterface $baseFixture,
        protected GeneratorInterface $generator
    )
    {
    }

    /**
     * @param GeneratorInterface $prototype
     * @param int|null $baseIndex
     * @return list<array<string,mixed>>
     */
    public function generateRecords(GeneratorInterface $prototype, ?int $baseIndex = null): array
    {
        $generator = new GeneratorComposite([$prototype, $this->generator]);
        return $this->baseFixture->generateRecords($generator, $baseIndex);
    }
}
