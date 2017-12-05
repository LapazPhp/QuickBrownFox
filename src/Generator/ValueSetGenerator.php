<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueNormalizer;
use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class ValueSetGenerator implements GeneratorInterface
{
    /**
     * @var ValueProviderInterface[]
     */
    protected $valueProviders;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->valueProviders = ValueNormalizer::ensureValueProviders($values);
    }

    /**
     * @param $index
     * @return ValueProviderInterface[]
     */
    public function extractAt($index)
    {
        return $this->valueProviders;
    }

    /**
     * @param int $index
     * @return array
     */
    public function generateAt($index)
    {
        $record = [];
        foreach ($this->valueProviders as $field => $valueProvider) {
            $record[$field] = $valueProvider->getAt($index);
        }
        return $record;
    }
}
