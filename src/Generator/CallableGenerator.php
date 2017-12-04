<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class CallableGenerator implements GeneratorInterface
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
     * @param int $index
     * @return ValueProviderInterface[]
     */
    public function extractAt($index)
    {
        // ugly...
        $values = $this->generateAt($index);
        return (new ValueSetGenerator($values))->extractAt($index);
    }

    /**
     * @param int $index
     * @return array
     */
    public function generateAt($index)
    {
        return call_user_func($this->callable, $index);
    }
}
