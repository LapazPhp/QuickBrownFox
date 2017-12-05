<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomBoolean extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        return $this->randomValueGenerator->boolean();
    }
}
