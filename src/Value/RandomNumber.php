<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomNumber extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        $length = $this->column->getLength();
        // Digits must be within integer limit.
        $length = min($length, strlen(mt_getrandmax()) - 1);
        return $this->fakerDataGenerator->randomNumber($length);
    }
}
