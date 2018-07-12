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
     * @var FixtureLoadableInterface
     */
    protected $fixtureLoader;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var FixtureRepository
     */
    protected $fixtureRepository;

    /**
     * @param FixtureLoadableInterface $fixtureLoader
     * @param string $table
     * @param FixtureRepository $fixtureRepository
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(
        FixtureLoadableInterface $fixtureLoader,
        $table,
        FixtureRepository $fixtureRepository,
        GeneratorRepository $generatorRepository
    )
    {
        $this->fixtureLoader = $fixtureLoader;
        $this->table = $table;
        $this->fixtureRepository = $fixtureRepository;

        $this->generatorRepository = $generatorRepository;
        $this->generators = [];
    }

    /**
     * Inserts records filled by data produced by stacked Generators.
     *
     * @param int $amount
     * @param int $baseIndex
     * @return array
     */
    public function generate($amount = 1, $baseIndex = 0)
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
     * @param string|array $fixture
     * @param int|null $baseIndex
     * @return array
     */
    public function load($fixture, $baseIndex = null)
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
