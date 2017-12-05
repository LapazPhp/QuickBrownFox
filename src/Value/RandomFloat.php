<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomFloat extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        return $this->randomValueGenerator->randomFloat();
    }
}
