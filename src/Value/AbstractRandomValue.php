<?php
namespace Lapaz\QuickBrownFox\Value;

use Doctrine\DBAL\Schema\Column;
use Faker\Generator as RandomValueGenerator;

abstract class AbstractRandomValue implements ValueProviderInterface
{
    /**
     * @var Column
     */
    protected $column;

    /**
     * @var RandomValueGenerator
     */
    protected $randomValueGenerator;

    /**
     * @param RandomValueGenerator $randomValueGenerator
     * @param Column $column
     */
    public function __construct(RandomValueGenerator $randomValueGenerator, Column $column)
    {
        $this->randomValueGenerator = $randomValueGenerator;
        $this->column = $column;
    }
}
