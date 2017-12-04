<?php
namespace Lapaz\QuickBrownFox\Value;

class ValueNormalizer
{
    /**
     * @param mixed $value
     * @return ValueProviderInterface
     */
    public static function ensureValueProvider($value)
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
     * @param array $values
     * @return array
     */
    public static function ensureValueProviders(array $values)
    {
        return array_map([static::class, 'ensureValueProvider'], $values);
    }
}
