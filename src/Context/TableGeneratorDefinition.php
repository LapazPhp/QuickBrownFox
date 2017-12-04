<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class TableGeneratorDefinition
{
    use WithContextTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct($name, GeneratorRepository $generatorRepository)
    {
        $this->name = $name;
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

        $this->generatorRepository->defineComposite($this->name, $generators);
    }
}
