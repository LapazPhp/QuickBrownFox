<?php
namespace Lapaz\QuickBrownFox\Value;

use Doctrine\DBAL\Schema\Column;
use Faker\Generator;

abstract class AbstractRandomValue implements ValueProviderInterface
{
    /**
     * @var Column
     */
    protected $column;
    /**
     * @var Generator
     */
    protected $fakerDataGenerator;

    /**
     * @param Generator $fakerDataGenerator
     * @param Column $column
     */
    public function __construct(Generator $fakerDataGenerator, Column $column)
    {
        $this->fakerDataGenerator = $fakerDataGenerator;
        $this->column = $column;
    }
}
