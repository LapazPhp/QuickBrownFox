<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomDate extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        return \DateTime::createFromFormat(
            'Y-m-d',
            $this->randomValueGenerator->date()
        );
    }
}
