<?php
namespace Lapaz\QuickBrownFox\Value;

class ValueNormalizer
{
    /**
     * @param mixed $value
     * @return ValueProviderInterface
     */
    public static function ensureValueProvider(mixed $value): ValueProviderInterface
    {
        if (!($value instanceof ValueProviderInterface)) {
            if (!is_string($value) && is_callable($value)) {
                $value = new CallableValue($value);
            } else {
                $value = new FixedValue($value);
            }
        }

        return $value;
    }

    /**
     * @param list<mixed> $values
     * @return list<ValueProviderInterface>
     */
    public static function ensureValueProviders(array $values): array
    {
        return array_map([static::class, 'ensureValueProvider'], $values);
    }
}
