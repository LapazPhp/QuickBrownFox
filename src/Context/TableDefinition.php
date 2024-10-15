<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

/**
 * Table definition context object.
 */
class TableDefinition
{
    /**
     * @param FixtureRepository $fixtureRepository
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(
        protected FixtureRepository $fixtureRepository,
        protected GeneratorRepository $generatorRepository
    )
    {
    }

    /**
     * Starts the table default definition.
     *
     * @return TableDefaultsDefinition
     */
    public function defaults(): TableDefaultsDefinition
    {
        return new TableDefaultsDefinition($this->generatorRepository);
    }

    /**
     * Starts a predefined table generator definition.
     *
     * @param string $name
     * @return TableGeneratorDefinition
     */
    public function generator(string $name): TableGeneratorDefinition
    {
        return new TableGeneratorDefinition($name, $this->generatorRepository);
    }

    /**
     * Starts a predefined table fixture definition.
     *
     * @param string $name
     * @return TableFixtureDefinition
     */
    public function fixture(string $name): TableFixtureDefinition
    {
        return new TableFixtureDefinition($name, $this->fixtureRepository, $this->generatorRepository);
    }
}
