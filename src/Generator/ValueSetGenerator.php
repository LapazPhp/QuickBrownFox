<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueNormalizer;
use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class ValueSetGenerator implements GeneratorInterface
{
    /**
     * @var array<string,ValueProviderInterface>
     */
    protected array $valueProviders;

    /**
     * @param array<string,mixed> $values
     */
    public function __construct(array $values)
    {
        $this->valueProviders = ValueNormalizer::ensureValueProviders($values);
    }

    /**
     * @param int $index
     * @return array<string,ValueProviderInterface>
     */
    public function extractAt(int $index): array
    {
        return $this->valueProviders;
    }

    /**
     * @param int $index
     * @return array<string, mixed>
     */
    public function generateAt(int $index): array
    {
        $record = [];
        foreach ($this->valueProviders as $field => $valueProvider) {
            $record[$field] = $valueProvider->getAt($index);
        }
        return $record;
    }
}
