<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class TableDefaultsDefinition
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
     * @param callable|array $definition
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
