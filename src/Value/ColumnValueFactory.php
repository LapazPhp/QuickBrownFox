<?php
namespace Lapaz\QuickBrownFox\Value;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Faker\Generator;

class ColumnValueFactory
{
    const GENERATOR_MAPPING = [
        Type::TARRAY => UnsupportedType::class,
        Type::SIMPLE_ARRAY => UnsupportedType::class,
        Type::JSON_ARRAY => UnsupportedType::class,
        Type::BIGINT => RandomNumber::class,
        Type::BOOLEAN => RandomBoolean::class,
        Type::DATETIME => RandomDateTime::class,
        Type::DATETIMETZ => RandomDateTime::class,
        Type::DATE => RandomDate::class,
        Type::TIME => RandomTime::class,
        Type::DECIMAL => RandomNumber::class,
        Type::INTEGER => RandomNumber::class,
        Type::OBJECT => UnsupportedType::class,
        Type::SMALLINT => RandomNumber::class,
        Type::STRING => RandomString::class,
        Type::TEXT => RandomText::class,
        Type::BINARY => UnsupportedType::class,
        Type::BLOB => UnsupportedType::class,
        Type::FLOAT => RandomFloat::class,
        Type::GUID => UnsupportedType::class,
    ];

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Generator
     */
    protected $randomValueGenerator;

    /**
     * @param Connection $connection
     * @param Generator $randomValueGenerator
     */
    public function __construct(Connection $connection, Generator $randomValueGenerator)
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
