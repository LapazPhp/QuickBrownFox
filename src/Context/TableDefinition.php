<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;
use Lapaz\QuickBrownFox\TableDefinitionInterface;

/**
 * Table definition context object.
 */
class TableDefinition implements TableDefinitionInterface
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
     * @return TableDefinitionDefaults
     */
    public function defaults(): TableDefinitionDefaults
    {
        return new TableDefinitionDefaults($this->generatorRepository);
    }

    /**
     * Starts a predefined table generator definition.
     *
     * @param string $name
     * @return TableDefinitionGenerator
     */
    public function generator(string $name): TableDefinitionGenerator
    {
        return new TableDefinitionGenerator($name, $this->generatorRepository);
    }

    /**
     * Starts a predefined table fixture definition.
     *
     * @param string $name
     * @return TableDefinitionFixture
     */
    public function fixture(string $name): TableDefinitionFixture
    {
        return new TableDefinitionFixture($name, $this->fixtureRepository, $this->generatorRepository);
    }
}
