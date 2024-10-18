<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;
use Lapaz\QuickBrownFox\TableDefinitionFixtureInterface;

class TableDefinitionFixture implements TableDefinitionFixtureInterface
{
    use WithContextTrait;

    /**
     * @param string $name
     * @param FixtureRepository $fixtureRepository
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(
        protected string $name,
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
    public function define(array $records): void
    {
        $this->fixtureRepository->define($this->name, $records, $this->generators);
    }

    /**
     * @param callable|array|string $generator
     * @param int $repeatAmount
     * @param int $baseIndex
     */
    public function defineGenerated(
        callable|array|string $generator,
        int $repeatAmount,
        int $baseIndex = 0
    ): void
    {
        $this->fixtureRepository->defineGenerated($this->name, $generator, $repeatAmount, $baseIndex, $this->generators);
    }
}
