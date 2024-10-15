<?php
namespace Lapaz\QuickBrownFox\Value;

class RandomString extends AbstractRandomValue
{
    const MIN_MULTI_WORD_LENGTH = 16;
    // Faker requires length >= 5 for ->text()

    /**
     * @inheritdoc
     */
    public function getAt(int $index): string
    {
        $length = $this->column->getLength();

        if ($this->column->getFixed()) {
            return $this->randomValueGenerator->lexify(str_repeat('?', $length));
        } elseif ($length < static::MIN_MULTI_WORD_LENGTH) {
            $length = $this->randomValueGenerator->numberBetween((int)ceil($length / 2.0), $length);
            return $this->randomValueGenerator->lexify(str_repeat('?', $length));
        } else {
            $text = $this->randomValueGenerator->text($length + 1);
            return substr(rtrim($text, '. '), 0, $length);
        }
    }
}
