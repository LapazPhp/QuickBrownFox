<?php
namespace Lapaz\QuickBrownFox\Fixture;

use Lapaz\QuickBrownFox\Exception\InvalidArgumentException;
use Lapaz\QuickBrownFox\Exception\UnexpectedStateException;
use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class FixtureRepository
{
    /**
     * @var GeneratorRepository
     */
    protected $generatorRepository;

    /**
     * @var FixtureInterface[]
     */
    protected $items;

    /**
     * @var bool
     */
    protected $locked;

    /**
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(GeneratorRepository $generatorRepository)
    {
        $this->generatorRepository = $generatorRepository;
        $this->items = [];
        $this->locked = false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->items[$name]);
    }

    /**
     * @param string $name
     * @return FixtureInterface
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException("No such definition: " . $name);
        }

        $this->locked = true;

        return $this->items[$name];
    }

    /**
     * @param string $name
     * @param FixtureInterface $generator
     */
    public function set($name, FixtureInterface $generator)
    {
        if ($this->locked) {
            throw new UnexpectedStateException("Modification not allowed");
        }

        if (isset($this->items[$name])) {
            throw new UnexpectedStateException("Already defined: " . $name);
        }

        $this->items[$name] = $generator;
    }

    /**
     * @param array $records
     * @param GeneratorInterface[]|string[] $defaultValueGenerators
     * @return FixedArrayFixture
     */
    public function newFixture($records, $defaultValueGenerators = [])
    {
        $fixtureSource = new FixedArrayFixture($records);

        if (!empty($defaultValueGenerators)) {
            $fixtureSource = $this->newGeneratorFollowedFixture($fixtureSource, $defaultValueGenerators);
        }

        return $fixtureSource;
    }

    /**
     * @param GeneratorInterface|string|array|callable $generator
     * @param int $repeatAmount
     * @param int $baseIndex
     * @param GeneratorInterface[]|string[] $defaultValueGenerators
     * @return GeneratedRecordFixture
     */
    public function newGeneratedFixture($generator, $repeatAmount, $baseIndex, $defaultValueGenerators = [])
    {
        $fixtureSource = new GeneratedRecordFixture(
            $this->normalizeGenerator($generator),
            $repeatAmount,
            $baseIndex
        );

        if (!empty($defaultValueGenerators)) {
            $fixtureSource = $this->newGeneratorFollowedFixture($fixtureSource, $defaultValueGenerators);
        }

        return $fixtureSource;
    }

    /**
     * @param FixtureInterface $fixtureSource
     * @param GeneratorInterface[]|string[] $generators
     * @return GeneratorSupportedFixture
     */
    public function newGeneratorFollowedFixture($fixtureSource, $generators)
    {
        $generators = array_map([$this, 'normalizeGenerator'], $generators);
        return new GeneratorSupportedFixture(
            $fixtureSource,
            new GeneratorComposite($generators)
        );
    }

    /**
     * @param string $name
     * @param array $records
     * @param GeneratorInterface[]|string[] $defaultValueGenerators
     */
    public function define($name, $records, $defaultValueGenerators = [])
    {
        $this->set($name, $this->newFixture($records, $defaultValueGenerators));
    }

    /**
     * @param string $name
     * @param GeneratorInterface|string|array|callable $generator
     * @param int $repeatAmount
     * @param int $baseIndex
     * @param GeneratorInterface[]|string[] $defaultValueGenerators
     */
    public function defineGenerated($name, $generator, $repeatAmount, $baseIndex = 0, $defaultValueGenerators = [])
    {
        $this->set($name, $this->newGeneratedFixture($generator, $repeatAmount, $baseIndex, $defaultValueGenerators));
    }

    // defineGeneratorFollowed ?

    /**
     * @param GeneratorInterface|string|array|callable $generator
     * @return GeneratorInterface
     */
    protected function normalizeGenerator($generator)
    {
        return $this->generatorRepository->normalizeGenerator($generator);
    }
}
