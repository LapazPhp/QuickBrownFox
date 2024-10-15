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
     * @var array<string, FixtureInterface>
     */
    protected array $items;

    /**
     * @var bool
     */
    protected bool $locked;

    /**
     * @param GeneratorRepository $generatorRepository
     */
    public function __construct(
        protected GeneratorRepository $generatorRepository
    )
    {
        $this->items = [];
        $this->locked = false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    /**
     * @param string $name
     * @return FixtureInterface
     */
    public function get(string $name): FixtureInterface
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
    public function set(string $name, FixtureInterface $generator): void
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
     * @param list<array<string,mixed>> $records
     * @param list<GeneratorInterface|string> $defaultValueGenerators
     * @return FixedArrayFixture
     */
    public function newFixture(array $records, array $defaultValueGenerators = []): FixedArrayFixture
    {
        $fixtureSource = new FixedArrayFixture($records);

        if (!empty($defaultValueGenerators)) {
            $fixtureSource = $this->newGeneratorFollowedFixture($fixtureSource, $defaultValueGenerators);
        }

        return $fixtureSource;
    }

    /**
     * @param callable|array|string|GeneratorInterface $generator
     * @param int $repeatAmount
     * @param int $baseIndex
     * @param GeneratorInterface[]|string[] $defaultValueGenerators
     * @return GeneratedRecordFixture
     */
    public function newGeneratedFixture(
        callable|array|string|GeneratorInterface $generator,
        int $repeatAmount,
        int $baseIndex,
        array $defaultValueGenerators = []
    ): GeneratedRecordFixture
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
     * @param list<GeneratorInterface|string> $generators
     * @return GeneratorSupportedFixture
     */
    public function newGeneratorFollowedFixture(FixtureInterface $fixtureSource, array $generators): GeneratorSupportedFixture
    {
        $generators = array_map([$this, 'normalizeGenerator'], $generators);
        return new GeneratorSupportedFixture(
            $fixtureSource,
            new GeneratorComposite($generators)
        );
    }

    /**
     * @param string $name
     * @param list<array<string,mixed>> $records
     * @param list<GeneratorInterface|string> $defaultValueGenerators
     */
    public function define(
        string $name,
        array $records,
        array $defaultValueGenerators = []
    ): void
    {
        $this->set($name, $this->newFixture($records, $defaultValueGenerators));
    }

    /**
     * @param string $name
     * @param callable|array|string|GeneratorInterface $generator
     * @param int $repeatAmount
     * @param int $baseIndex
     * @param list<GeneratorInterface|string> $defaultValueGenerators
     */
    public function defineGenerated(
        string $name,
        callable|array|string|GeneratorInterface $generator,
        int $repeatAmount,
        int $baseIndex = 0,
        array $defaultValueGenerators = []
    ): void
    {
        $this->set($name, $this->newGeneratedFixture($generator, $repeatAmount, $baseIndex, $defaultValueGenerators));
    }

    // defineGeneratorFollowed ?

    /**
     * @param callable|array|string|GeneratorInterface $generator
     * @return GeneratorInterface
     */
    protected function normalizeGenerator(callable|array|string|GeneratorInterface $generator): GeneratorInterface
    {
        return $this->generatorRepository->normalizeGenerator($generator);
    }
}
