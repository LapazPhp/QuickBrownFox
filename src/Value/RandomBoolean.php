<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomBoolean extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt(int $index): bool
    {
        return $this->randomValueGenerator->boolean();
    }
}
