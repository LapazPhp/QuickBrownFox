<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Exception\InvalidArgumentException;
use Lapaz\QuickBrownFox\Exception\UnexpectedStateException;

class GeneratorRepository
{
    /**
     * @var GeneratorInterface
     */
    protected $tableDefaults;

    /**
     * @var GeneratorInterface[]
     */
    protected $items;

    /**
     * @var bool
     */
    protected $locked;

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
    public function has($name)
    {
        return isset($this->items[$name]);
    }

    /**
     * @param string $name
     * @return GeneratorInterface
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
     * @param GeneratorInterface $generator
     */
    public function set($name, GeneratorInterface $generator)
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
    public function getTableDefaults()
    {
        $this->locked = true;

        return $this->tableDefaults;
    }

    /**
     * @param GeneratorInterface|null $generator
     */
    public function setTableDefaults($generator)
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
     * @param array|callable $definition
     * @return GeneratorInterface
     */
    public function newGenerator($definition)
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
     * @param GeneratorInterface[]|string[]|array[]|callable[] $generators
     * @return GeneratorComposite
     */
    public function newGeneratorComposite(array $generators)
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
     * @param GeneratorInterface[]|string[]|array[]|callable[] $generators
     */
    public function defineComposite($name, array $generators)
    {
        $this->set($name, $this->newGeneratorComposite($generators));
    }

    /**
     * @param GeneratorInterface[]|string[]|array[]|callable[] $generators
     */
    public function defineTableDefaults($generators)
    {
        $this->setTableDefaults($this->newGeneratorComposite($generators));
    }

    /**
     * @param GeneratorInterface|string|array|callable $generator
     * @return GeneratorInterface
     */
    public function normalizeGenerator($generator)
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
