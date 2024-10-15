<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;
use Lapaz\QuickBrownFox\Generator\ValueSetGenerator;

class FixedArrayFixture implements FixtureInterface
{
    /**
     * @param list<array<string,mixed>> $records
     */
    public function __construct(
        protected array $records
    ) {
    }

    /**
     * @param GeneratorInterface $prototype
     * @param int|null $baseIndex
     * @return list<array<string,mixed>>
     */
    public function generateRecords(GeneratorInterface $prototype, ?int $baseIndex = null): array
    {
        if ($baseIndex === null) {
            $baseIndex = 0;
        }
        $records = [];
        foreach ($this->records as $i => $record) {
            $generator = new GeneratorComposite([
                $prototype,
                new ValueSetGenerator($record),
            ]);
            $records[] = $generator->generateAt($baseIndex + $i);
        }
        return $records;
    }
}
