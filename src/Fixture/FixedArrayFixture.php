<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Generator\ValueSetGenerator;
use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;

class FixedArrayFixture implements FixtureInterface
{
    /**
     * @var array
     */
    protected $records;

    /**
     * @param array $records
     */
    public function __construct(array $records)
    {
        $this->records = $records;
    }

    /**
     * @param GeneratorInterface $prototype
     * @param int $baseIndex
     * @return array
     */
    public function generateRecords(GeneratorInterface $prototype, $baseIndex = null)
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
