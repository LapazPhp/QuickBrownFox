<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomText extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt(int $index): string
    {
        return $this->randomValueGenerator->paragraph();
    }
}
