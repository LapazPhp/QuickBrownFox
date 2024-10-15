<?php
namespace Lapaz\QuickBrownFox\Value;

class FixedValue implements ValueProviderInterface
{
    /**
     * @param mixed $value
     */
    public function __construct(
        protected mixed $value
    )
    {
    }

    /**
     * @inheritdoc
     */
    public function getAt(int $index): mixed
    {
        return $this->value;
    }
}
