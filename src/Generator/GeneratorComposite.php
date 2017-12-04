<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class GeneratorComposite implements GeneratorInterface
{
    /**
     * @var GeneratorInterface[]
     */
    protected $generators;

    /**
     * @param GeneratorInterface[] $generators
     */
    public function __construct($generators)
    {
        $this->generators = $generators;
    }

    /**
     * @param int $index
     * @return ValueProviderInterface[]
     */
    public function extractAt($index)
    {
        $valueProviders = [];
        foreach ($this->generators as $generator) {
            $valueProviders = array_merge($valueProviders, $generator->extractAt($index));
        }
        return $valueProviders;
    }

    /**
     * @param int $index
     * @return array
     */
    public function generateAt($index)
    {
        $record = [];
        foreach ($this->generators as $generator) {
            $record = array_merge($record, $generator->generateAt($index));
        }
        return $record;
    }
}
