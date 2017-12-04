<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class TableDefinition
{
    /**
     * @var FixtureRepository
     */
    protected $fixtureRepository;

    /**
     * @var GeneratorRepository
     */
    protected $generatorRepository;

    /**
     * @param FixtureRepository $fixtureRepository
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(FixtureRepository $fixtureRepository, GeneratorRepository $generatorRepository)
    {
        $this->fixtureRepository = $fixtureRepository;
        $this->generatorRepository = $generatorRepository;
    }

    /**
     * @return TableDefaultsDefinition
     */
    public function defaults()
    {
        return new TableDefaultsDefinition($this->generatorRepository);
    }

    /**
     * @param string $name
     * @return TableGeneratorDefinition
     */
    public function generator($name)
    {
        return new TableGeneratorDefinition($name, $this->generatorRepository);
    }

    /**
     * @param string $name
     * @return TableFixtureDefinition
     */
    public function fixture($name)
    {
        return new TableFixtureDefinition($name, $this->fixtureRepository, $this->generatorRepository);
    }
}
