<?php
namespace Lapaz\QuickBrownFox\Value;

use DateTime;

class RandomTime extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt(int $index): DateTime
    {
        return DateTime::createFromFormat(
            'H:i:s',
            $this->randomValueGenerator->time()
        );
    }
}
