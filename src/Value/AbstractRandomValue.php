<?php
namespace Lapaz\QuickBrownFox\Value;

use Doctrine\DBAL\Schema\Column;
use Faker\Generator as RandomValueGenerator;

abstract class AbstractRandomValue implements ValueProviderInterface
{
    /**
     * @param RandomValueGenerator $randomValueGenerator
     * @param Column $column
     */
    public function __construct(
        protected RandomValueGenerator $randomValueGenerator,
        protected Column $column
    )
    {
    }
}
