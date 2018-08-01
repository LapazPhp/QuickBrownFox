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
        $length = min($length, $this->randomValueGenerator->numberBetween(5, $length));

        if ($this->column->getFixed()) {
            return $this->randomValueGenerator->lexify(str_repeat('?', $length));
        } else {
            return $this->randomValueGenerator->text($length);
        }
    }
}
