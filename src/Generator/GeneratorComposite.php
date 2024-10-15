<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class GeneratorComposite implements GeneratorInterface
{
    /**
     * @param list<GeneratorInterface> $generators
     */
    public function __construct(
        protected array $generators
    ) {
    }

    /**
     * @param int $index
     * @return list<ValueProviderInterface>
     */
    public function extractAt(int $index): array
    {
        $valueProviders = [];
        foreach ($this->generators as $generator) {
            $valueProviders = array_merge($valueProviders, $generator->extractAt($index));
        }
        return $valueProviders;
    }

    /**
     * @param int $index
     * @return array<string,mixed>
     */
    public function generateAt(int $index): array
    {
        $record = [];
        foreach ($this->generators as $generator) {
            $record = array_merge($record, $generator->generateAt($index));
        }
        return $record;
    }
}
