<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixedArrayFixture;
use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Fixture\GeneratedRecordFixture;
use Lapaz\QuickBrownFox\Fixture\GeneratorSupportedFixture;
use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;
use Lapaz\QuickBrownFox\TableLoadingInterface;

/**
 * Fixture loading context for the table.
 */
class TableLoading implements TableLoadingInterface
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
     * @inheritDoc
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
     * @inheritDoc
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
