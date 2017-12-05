<?php
namespace Lapaz\QuickBrownFox\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Faker\Generator as RandomValueGenerator;
use Lapaz\QuickBrownFox\Value\ColumnValueFactory;
use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class TablePrototypeGeneratorBuilder
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var RandomValueGenerator
     */
    protected $randomValueGenerator;

    /**
     * @var ColumnValueFactory
     */
    protected $columnValueFactory;

    /**
     * @param Connection $connection
     * @param RandomValueGenerator $randomValueGenerator
     */
    public function __construct(Connection $connection, RandomValueGenerator $randomValueGenerator)
    {
        $this->connection = $connection;
        $this->randomValueGenerator = $randomValueGenerator;
        $this->columnValueFactory = new ColumnValueFactory($connection, $randomValueGenerator);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $table
     * @return GeneratorInterface
     */
    public function build($table)
    {
        return new ValueSetGenerator(array_merge(
            $this->normalColumnValueProviders($table),
            $this->foreignKeyColumnValueProviders($table)
        ));
    }

    /**
     * @param string $table
     * @return ValueProviderInterface[]
     */
    protected function normalColumnValueProviders($table)
    {
        $schemaManager = $this->connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns($table);

        $valueProviders = [];
        foreach ($columns as $column) {
            $name = $column->getName();
            if ($this->isValueRequiredFor($column)) {
                $valueProviders[$name] = $this->columnValueFactory->createFor($column);
            }
        }
        return $valueProviders;
    }

    /**
     * @param string $table
     * @return ValueProviderInterface[]
     */
    protected function foreignKeyColumnValueProviders($table)
    {
        $schemaManager = $this->connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns($table);

        $foreignKeys = $schemaManager->listTableForeignKeys($table);

        $valueProviders = [];
        foreach ($foreignKeys as $foreignKey) {
            $fkLocalColumns = array_map(function ($name) use ($columns) {
                return $columns[$name];
            }, $foreignKey->getLocalColumns());

            if ($this->isValueRequiredAny($fkLocalColumns)) {
                $fetcher = new ForeignTableFetcher(
                    $foreignKey->getForeignTableName(),
                    array_combine($foreignKey->getLocalColumns(), $foreignKey->getForeignColumns()),
                    $this
                );
                $foreignKeyReferencedValues = $fetcher->createValueProviders();

                foreach ($foreignKeyReferencedValues as $column => $valueProvider) {
                    $valueProviders[$column] = $valueProvider;
                }
            }
        }
        return $valueProviders;
    }

    /**
     * @param Column $column
     * @return bool
     */
    protected function isValueRequiredFor(Column $column)
    {
        return $column->getNotnull() && $column->getDefault() === null && !$column->getAutoincrement();
    }

    /**
     * @param Column[] $columns
     * @return bool
     */
    protected function isValueRequiredAny(array $columns)
    {
        foreach ($columns as $column) {
            if ($this->isValueRequiredFor($column)) {
                return true;
            }
        }
        return false;
    }
}
