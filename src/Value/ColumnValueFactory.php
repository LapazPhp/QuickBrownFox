<?php
namespace Lapaz\QuickBrownFox\Value;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\AsciiStringType;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\EnumType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\SimpleArrayType;
use Doctrine\DBAL\Types\SmallFloatType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\TimeType;
use Faker\Generator as RandomValueGenerator;

class ColumnValueFactory
{
    private const GENERATOR_MAPPING = [
        AsciiStringType::class => RandomString::class,
        BigIntType::class => RandomNumber::class,
        BinaryType::class => UnsupportedType::class,
        BlobType::class => UnsupportedType::class,
        BooleanType::class => RandomBoolean::class,
        DateType::class => RandomDate::class,
        DateImmutableType::class => RandomDate::class,
        DateIntervalType::class => UnsupportedType::class,
        DateTimeType::class => RandomDateTime::class,
        DateTimeImmutableType::class => RandomDateTime::class,
        DateTimeTzType::class => RandomDateTime::class,
        DateTimeTzImmutableType::class => RandomDateTime::class,
        DecimalType::class => RandomDecimal::class,
        EnumType::class => UnsupportedType::class,
        FloatType::class => RandomDecimal::class,
        GuidType::class => UnsupportedType::class,
        IntegerType::class => RandomNumber::class,
        JsonType::class => UnsupportedType::class,
        SimpleArrayType::class => UnsupportedType::class,
        SmallFloatType::class => RandomDecimal::class,
        SmallIntType::class => RandomNumber::class,
        StringType::class => RandomString::class,
        TextType::class => RandomText::class,
        TimeType::class => RandomTime::class,
        TimeImmutableType::class => RandomTime::class,
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
    public function createFor(Column $column): ValueProviderInterface
    {
        $typeClass = get_class($column->getType());
        if (!isset(static::GENERATOR_MAPPING[$typeClass])) {
            throw new \InvalidArgumentException("Unsupported column type: $typeClass");
        }

        $class = static::GENERATOR_MAPPING[$typeClass];
        return new $class($this->randomValueGenerator, $column);
    }
}
