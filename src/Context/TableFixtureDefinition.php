<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class TableFixtureDefinition
{
    use WithContextTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var FixtureRepository
     */
    protected $fixtureRepository;

    /**
     * @param string $name
     * @param FixtureRepository $fixtureRepository
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct($name, FixtureRepository $fixtureRepository, GeneratorRepository $generatorRepository)
    {
        $this->name = $name;
        $this->fixtureRepository = $fixtureRepository;
        $this->generatorRepository = $generatorRepository;
        $this->generators = [];
    }

    /**
     * @param array $records
     */
    public function define($records)
    {
        $this->fixtureRepository->define($this->name, $records, $this->generators);
    }

    /**
     * @param string|array|callable $generator
     * @param int $repeatAmount
     * @param int $baseIndex
     */
    public function defineGenerated($generator, $repeatAmount, $baseIndex = 0)
    {
        $this->fixtureRepository->defineGenerated($this->name, $generator, $repeatAmount, $baseIndex, $this->generators);
    }
}
