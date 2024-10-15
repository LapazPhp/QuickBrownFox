<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixedArrayFixture;
use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Fixture\GeneratedRecordFixture;
use Lapaz\QuickBrownFox\Fixture\GeneratorSupportedFixture;
use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

/**
 * Fixture loading context for the table.
 */
class TableLoading
{
    use WithContextTrait;

    /**
     * @param FixtureLoadableInterface $fixtureLoader
     * @param string $table
     * @param FixtureRepository $fixtureRepository
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(
        protected FixtureLoadableInterface $fixtureLoader,
        protected string $table,
        protected FixtureRepository $fixtureRepository,
        GeneratorRepository $generatorRepository
    )
    {
        $this->generatorRepository = $generatorRepository;
        $this->generators = [];
    }

    /**
     * Inserts records filled by data produced by stacked Generators.
     *
     * @param int $amount
     * @param int $baseIndex
     * @return list<int|string>
     */
    public function generate(int $amount = 1, int $baseIndex = 0): array
    {
        $fixtureSource = new GeneratedRecordFixture(
            new GeneratorComposite($this->generators),
            $amount
        );

        return $this->fixtureLoader->load($this->table, $fixtureSource, $baseIndex);
    }

    /**
     * Inserts records filled by fixed data specified by array.
     * Unspecified column value produced by Generator stack implicitly.
     *
     * @param array|string $fixture
     * @param int|null $baseIndex
     * @return list<int|string>
     */
    public function load(array|string $fixture, ?int $baseIndex = null): array
    {
        if (is_array($fixture)) {
            $fixtureSource = new FixedArrayFixture($fixture);
        } else {
            $fixtureSource = $this->fixtureRepository->get($fixture);
        }

        $fixtureSource = new GeneratorSupportedFixture(
            $fixtureSource,
            new GeneratorComposite($this->generators)
        );

        return $this->fixtureLoader->load($this->table, $fixtureSource, $baseIndex);
    }
}
