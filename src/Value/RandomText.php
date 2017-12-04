<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomText extends AbstractRandomValue
{
    /**
     * @inheritdoc
     */
    public function getAt($index)
    {
        return $this->fakerDataGenerator->paragraph();
    }
}
