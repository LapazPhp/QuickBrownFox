<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomDateTime extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        return $this->randomValueGenerator->dateTime();
    }
}
