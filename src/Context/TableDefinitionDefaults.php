<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Generator\GeneratorRepository;
use Lapaz\QuickBrownFox\TableDefinitionGeneratorInterface;

class TableDefinitionDefaults implements TableDefinitionGeneratorInterface
{
    use WithContextTrait;

    /**
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(GeneratorRepository $generatorRepository)
    {
        $this->generatorRepository = $generatorRepository;
        $this->generators = [];
    }

    /**
     * @inheritDoc
     */
    public function define(callable|array $definition = []): void
    {
        $generators = $this->generators;

        if (!empty($definition)) {
            $generators[] = $definition;
        }

        $this->generatorRepository->defineTableDefaults($generators);
    }
}
