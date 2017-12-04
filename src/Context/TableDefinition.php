<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class TableDefinition
{
    use WithContextTrait;

    /**
     * @var FixtureRepository
     */
    protected $fixtureRepository;

    /**
     * @param FixtureRepository $fixtureRepository
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(FixtureRepository $fixtureRepository, GeneratorRepository $generatorRepository)
    {
        $this->fixtureRepository = $fixtureRepository;
        $this->generatorRepository = $generatorRepository;
        $this->generators = [];
    }

    /**
     * @param array|callable $definition
     */
    public function defaults($definition = [])
    {
        $generators = $this->generators;

        if (!empty($definition)) {
            $generators[] = $definition;
        }

        $this->generatorRepository->defineTableDefaults($generators);
    }

    /**
     * @param string $name
     * @param array|callable $definition
     */
    public function generator($name, $definition = [])
    {
        $generators = $this->generators;

        if (!empty($definition)) {
            $generators[] = $definition;
        }

        $this->generatorRepository->defineComposite($name, $generators);
    }

    /**
     * @param string $name
     * @param array $records
     */
    public function fixture($name, $records)
    {
        $this->fixtureRepository->define($name, $records, $this->generators);
    }

    /**
     * @param string $name
     * @param string|array|callable $generator
     * @param int $repeatAmount
     * @param int $baseIndex
     */
    public function fixtureGenerated($name, $generator, $repeatAmount, $baseIndex = 0)
    {
        $this->fixtureRepository->defineGenerated($name, $generator, $repeatAmount, $baseIndex, $this->generators);
    }
}
