<?php
namespace Lapaz\QuickBrownFox\Value;

use DateTime;

class RandomDate extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt(int $index): DateTime
    {
        return DateTime::createFromFormat(
            'Y-m-d',
            $this->randomValueGenerator->date()
        );
    }
}
