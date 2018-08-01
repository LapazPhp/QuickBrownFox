<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomDecimal extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        $precision = $this->column->getPrecision();
        $scale = $this->column->getScale();
        $max = pow(10.0, $precision - $scale);
        $value = $this->randomValueGenerator->randomFloat($scale, 0, $max);
        if (!$this->column->getUnsigned() && $this->randomValueGenerator->boolean()) {
            return $value * -1;
        } else {
            return $value;
        }
    }
}
