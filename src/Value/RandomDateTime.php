<?php
namespace Lapaz\QuickBrownFox\Value;

use DateTime;

class RandomDateTime extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt(int $index): DateTime
    {
        return $this->randomValueGenerator->dateTime();
    }
}
