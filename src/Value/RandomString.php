<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomString extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        $length = $this->column->getLength();
        $length = min($length, $this->fakerDataGenerator->numberBetween(5, $length));
        return $this->fakerDataGenerator->text($length);
    }
}
