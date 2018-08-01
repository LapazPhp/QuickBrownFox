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
        return $this->randomValueGenerator->randomFloat($scale, 0, $max);
    }
}
