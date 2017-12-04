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
     * @param array|callable $definition
     */
    public function define($definition = [])
    {
        $generators = $this->generators;

        if (!empty($definition)) {
            $generators[] = $definition;
        }

        $this->generatorRepository->defineTableDefaults($generators);
    }
}
