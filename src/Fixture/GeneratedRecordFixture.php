<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;

class GeneratedRecordFixture implements FixtureInterface
{
    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var int
     */
    protected $repeatAmount;

    /**
     * @var int
     */
    protected $defaultBaseIndex;

    /**
     * @param GeneratorInterface $generator
     * @param int $repeatAmount
     * @param int $defaultBaseIndex
     */
    public function __construct(GeneratorInterface $generator, $repeatAmount, $defaultBaseIndex = 0)
    {
        $this->generator = $generator;
        $this->repeatAmount = $repeatAmount;
        $this->defaultBaseIndex = $defaultBaseIndex;
    }

    /**
     * @param GeneratorInterface $prototype
     * @param int|null $baseIndex
     * @return array
     */
    public function generateRecords(GeneratorInterface $prototype, $baseIndex = null)
    {
        if ($baseIndex === null) {
            $baseIndex = $this->defaultBaseIndex;
        }

        $generator = new GeneratorComposite([
            $prototype,
            $this->generator,
        ]);

        $records = [];
        for ($i = 0; $i < $this->repeatAmount; $i++) {
            $records[] = $generator->generateAt($i + $baseIndex);
        }
        return $records;
    }
}
