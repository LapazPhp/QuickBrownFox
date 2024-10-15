<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class TableGeneratorDefinition
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
     * @param callable|array $definition
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
