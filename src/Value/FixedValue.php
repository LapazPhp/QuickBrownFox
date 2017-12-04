<?php
namespace Lapaz\QuickBrownFox\Value;

class FixedValue implements ValueProviderInterface
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        return $this->value;
    }
}
