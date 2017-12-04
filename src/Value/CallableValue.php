<?php
namespace Lapaz\QuickBrownFox\Value;

class CallableValue implements ValueProviderInterface
{
    /**
     * @var callable
     */
    protected $callable;

    /**
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        return call_user_func($this->callable, $index);
    }
}
