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
     * @inheritDoc
     */
    public function defaults(): TableDefinitionDefaults
    {
        return new TableDefinitionDefaults($this->generatorRepository);
    }

    /**
     * @inheritDoc
     */
    public function generator(string $name): TableDefinitionGenerator
    {
        return new TableDefinitionGenerator($name, $this->generatorRepository);
    }

    /**
     * @inheritDoc
     */
    public function fixture(string $name): TableDefinitionFixture
    {
        return new TableDefinitionFixture($name, $this->fixtureRepository, $this->generatorRepository);
    }
}
