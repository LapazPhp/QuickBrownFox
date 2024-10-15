<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomNumber extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt(int $index): int
    {
        $length = $this->column->getLength();
        // Digits must be within integer limit.
        $length = min($length, strlen(mt_getrandmax()) - 1);
        $value = $this->randomValueGenerator->randomNumber($length);
        if (!$this->column->getUnsigned() && $this->randomValueGenerator->boolean()) {
            return $value * -1;
        } else {
            return $value;
        }
    }
}
