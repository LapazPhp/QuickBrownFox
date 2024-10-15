<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;

class GeneratedRecordFixture implements FixtureInterface
{
    /**
     * @param GeneratorInterface $generator
     * @param int $repeatAmount
     * @param int|null $defaultBaseIndex
     */
    public function __construct(
        protected GeneratorInterface $generator,
        protected int $repeatAmount,
        protected ?int $defaultBaseIndex = 0
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
