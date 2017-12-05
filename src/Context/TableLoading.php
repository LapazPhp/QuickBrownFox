<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixedArrayFixture;
use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Fixture\GeneratedRecordFixture;
use Lapaz\QuickBrownFox\Fixture\GeneratorSupportedFixture;
use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;
use Lapaz\QuickBrownFox\LoaderSession;

class TableLoading
{
    use WithContextTrait;

    /**
     * @var LoaderSession
     */
    protected $session;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var FixtureRepository
     */
    protected $fixtureRepository;

    /**
     * @param LoaderSession $session
     * @param string $table
     * @param FixtureRepository $fixtureRepository
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(
        LoaderSession $session,
        $table,
        FixtureRepository $fixtureRepository,
        GeneratorRepository $generatorRepository
    )
    {
        $this->session = $session;
        $this->table = $table;
        $this->fixtureRepository = $fixtureRepository;

        $this->generatorRepository = $generatorRepository;
        $this->generators = [];

        $tableDefaults = $this->generatorRepository->getTableDefaults();
        if ($tableDefaults) {
            $this->generators[] = $tableDefaults;
        }
    }

    /**
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

        return $this->session->loadFixtureInternal($this->table, $fixtureSource, $baseIndex);
    }

    /**
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

        return $this->session->loadFixtureInternal($this->table, $fixtureSource, $baseIndex);
    }
}
