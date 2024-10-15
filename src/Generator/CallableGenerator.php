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
     * @return list<ValueProviderInterface>
     */
    public function extractAt(int $index): array
    {
        // ugly...
        $values = $this->generateAt($index);
        return (new ValueSetGenerator($values))->extractAt($index);
    }

    /**
     * @param int $index
     * @return array<string, mixed>
     */
    public function generateAt(int $index): array
    {
        return call_user_func($this->callable, $index);
    }
}
