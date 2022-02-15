<?php
namespace Lapaz\QuickBrownFox\Value;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;
use Faker\Generator as RandomValueGenerator;

class ColumnValueFactory
{
    const GENERATOR_MAPPING = [
        Types::ARRAY => UnsupportedType::class,
        Types::SIMPLE_ARRAY => UnsupportedType::class,
        Types::JSON => UnsupportedType::class,
        Types::BIGINT => RandomNumber::class,
        Types::BOOLEAN => RandomBoolean::class,
        Types::DATETIME_MUTABLE => RandomDateTime::class,
        Types::DATETIME_IMMUTABLE => RandomDateTime::class,
        Types::DATETIMETZ_MUTABLE => RandomDateTime::class,
        Types::DATETIMETZ_IMMUTABLE => RandomDateTime::class,
        Types::DATE_MUTABLE => RandomDate::class,
        Types::DATE_IMMUTABLE => RandomDate::class,
        Types::TIME_MUTABLE => RandomTime::class,
        Types::TIME_IMMUTABLE => RandomTime::class,
        Types::DECIMAL => RandomDecimal::class,
        Types::INTEGER => RandomNumber::class,
        Types::OBJECT => UnsupportedType::class,
        Types::SMALLINT => RandomNumber::class,
        Types::STRING => RandomString::class,
        Types::TEXT => RandomText::class,
        Types::BINARY => UnsupportedType::class,
        Types::BLOB => UnsupportedType::class,
        Types::FLOAT => RandomDecimal::class,
        Types::GUID => UnsupportedType::class,
    ];

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var RandomValueGenerator
     */
    protected $randomValueGenerator;

    /**
     * @param Connection $connection
     * @param RandomValueGenerator $randomValueGenerator
     */
    public function __construct(Connection $connection, RandomValueGenerator $randomValueGenerator)
    {
        $this->connection = $connection;
        $this->randomValueGenerator = $randomValueGenerator;
    }

    /**
     * @param Column $column
     * @return ValueProviderInterface
     */
    public function createFor(Column $column)
    {
        $class = static::GENERATOR_MAPPING[$column->getType()->getName()];
        return new $class($this->randomValueGenerator, $column);
    }
}
