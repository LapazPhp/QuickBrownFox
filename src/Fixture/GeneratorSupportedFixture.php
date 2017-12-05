<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;

class GeneratorSupportedFixture implements FixtureInterface
{
    /**
     * @var FixtureInterface
     */
    protected $baseFixture;

    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @param FixtureInterface $baseFixture
     * @param GeneratorInterface $generator
     */
    public function __construct(FixtureInterface $baseFixture, GeneratorInterface $generator)
    {
        $this->baseFixture = $baseFixture;
        $this->generator = $generator;
    }

    /**
     * @param GeneratorInterface $prototype
     * @param int|null $baseIndex
     * @return array
     */
    public function generateRecords(GeneratorInterface $prototype, $baseIndex = null)
    {
        $generator = new GeneratorComposite([$prototype, $this->generator]);
        return $this->baseFixture->generateRecords($generator, $baseIndex);
    }
}
