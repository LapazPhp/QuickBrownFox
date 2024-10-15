<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Exception\InvalidArgumentException;
use Lapaz\QuickBrownFox\Exception\UnexpectedStateException;

class GeneratorRepository
{
    /**
     * @var GeneratorInterface|null
     */
    protected ?GeneratorInterface $tableDefaults;

    /**
     * @var GeneratorInterface[]
     */
    protected array $items;

    /**
     * @var bool
     */
    protected bool $locked;

    /**
     *
     */
    public function __construct()
    {
        $this->tableDefaults = null;
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
     * @return GeneratorInterface
     */
    public function get(string $name): GeneratorInterface
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException("No such definition: " . $name);
        }

        $this->locked = true;

        return $this->items[$name];
    }

    /**
     * @param string $name
     * @param GeneratorInterface $generator
     */
    public function set(string $name, GeneratorInterface $generator): void
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
     * @return GeneratorInterface|null
     */
    public function getTableDefaults(): ?GeneratorInterface
    {
        $this->locked = true;

        return $this->tableDefaults;
    }

    /**
     * @param GeneratorInterface|null $generator
     */
    public function setTableDefaults(?GeneratorInterface $generator): void
    {
        if ($this->locked) {
            throw new UnexpectedStateException("Modification not allowed");
        }

        if (isset($this->tableDefault)) {
            throw new UnexpectedStateException("Table default generator already defined");
        }

        $this->tableDefaults = $generator;
    }

    /**
     * @param callable|array $definition
     * @return GeneratorInterface
     */
    public function newGenerator(callable|array $definition): GeneratorInterface
    {
        if (is_callable($definition)) {
            $generator = new CallableGenerator($definition);
        } elseif (is_array($definition)) {
            $generator = new ValueSetGenerator($definition);
        } else {
            throw new InvalidArgumentException("Bad definition");
        }
        return $generator;
    }

    /**
     * @param list<callable|array|string|GeneratorInterface> $generators
     * @return GeneratorInterface
     */
    public function newGeneratorComposite(array $generators): GeneratorInterface
    {
        $normalizedGenerators = [];
        foreach ($generators as $generator) {
            $normalizedGenerators[] = $this->normalizeGenerator($generator);
        }

        if (count($normalizedGenerators) === 1) {
            return $normalizedGenerators[0];
        }

        return new GeneratorComposite($normalizedGenerators);
    }

    /**
     * @param string $name
     * @param list<callable|array|string|GeneratorInterface> $generators
     */
    public function defineComposite(string $name, array $generators): void
    {
        $this->set($name, $this->newGeneratorComposite($generators));
    }

    /**
     * @param list<callable|array|string|GeneratorInterface> $generators
     */
    public function defineTableDefaults(array $generators): void
    {
        $this->setTableDefaults($this->newGeneratorComposite($generators));
    }

    /**
     * @param callable|array|string|GeneratorInterface $generator
     * @return GeneratorInterface
     */
    public function normalizeGenerator(callable|array|string|GeneratorInterface $generator): GeneratorInterface
    {
        if (is_array($generator) || is_callable($generator)) {
            $generator = $this->newGenerator($generator);
        } elseif (is_string($generator)) {
            $generator = new NamedGeneratorAccessor($this, $generator);
        }

        if (!($generator instanceof GeneratorInterface)) {
            throw new InvalidArgumentException("Invalid generator definition.");
        }

        return $generator;
    }
}
