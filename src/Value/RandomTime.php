<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomTime extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        return \DateTime::createFromFormat(
            'H:i:s',
            $this->randomValueGenerator->time()
        );
    }
}
