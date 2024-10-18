<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Generator\GeneratorRepository;
use Lapaz\QuickBrownFox\TableDefinitionGeneratorInterface;

class TableDefinitionGenerator implements TableDefinitionGeneratorInterface
{
    use WithContextTrait;

    /**
     * @param string $name
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(
        protected string $name,
        GeneratorRepository $generatorRepository
    )
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

        $this->generatorRepository->defineComposite($this->name, $generators);
    }
}
