<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Faker\Generator as RandomValueGenerator;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;
use Lapaz\QuickBrownFox\Generator\ValueSetGenerator;
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
     * @var Column[][]
     */
    protected $valueRequiredColumnsMap;

    /**
     * @var ForeignKeyConstraint[][]
     */
    protected $valueRequiredForeignKeysMap;

    /**
     * @param Connection $connection
     * @param RandomValueGenerator $randomValueGenerator
     */
    public function __construct(Connection $connection, RandomValueGenerator $randomValueGenerator)
    {
        $this->connection = $connection;
        $this->randomValueGenerator = $randomValueGenerator;
        $this->columnValueFactory = new ColumnValueFactory($connection, $randomValueGenerator);
        $this->valueRequiredColumnsMap = [];
        $this->valueRequiredForeignKeysMap = [];
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
        $valueProviders = [];
        foreach ($this->valueRequiredColumns($table) as $column) {
            $valueProviders[$column->getName()] = $this->columnValueFactory->createFor($column);
        }
        return $valueProviders;
    }

    /**
     * @param string $table
     * @return Column[]
     */
    protected function valueRequiredColumns($table)
    {
        if (!isset($this->valueRequiredColumnsMap[$table])) {
            $schemaManager = $this->connection->getSchemaManager();
            $columns = $schemaManager->listTableColumns($table);

            $valueRequiredColumns = [];
            foreach ($columns as $column) {
                if ($this->isValueRequiredFor($column)) {
                    $valueRequiredColumns[] = $column;
                }
            }
            $this->valueRequiredColumnsMap[$table] = $valueRequiredColumns;
        }
        return $this->valueRequiredColumnsMap[$table];
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
     * @param string $table
     * @return ValueProviderInterface[]
     */
    protected function foreignKeyColumnValueProviders($table)
    {
        $valueProviders = [];
        foreach ($this->valueRequiredForeignKeys($table) as $foreignKey) {
            // $fetcher must be created for every load() because it depends current table status.
            $fetcher = new ForeignTableFetcher(
                $foreignKey->getForeignTableName(),
                array_combine($foreignKey->getLocalColumns(), $foreignKey->getForeignColumns()),
                $this
            );
            foreach ($fetcher->createValueProviders() as $column => $valueProvider) {
                $valueProviders[$column] = $valueProvider;
            }
        }
        return $valueProviders;
    }

    /**
     * @param string $table
     * @return ForeignKeyConstraint[]
     */
    protected function valueRequiredForeignKeys($table)
    {
        if (!isset($this->valueRequiredForeignKeysMap[$table])) {
            $schemaManager = $this->connection->getSchemaManager();
            $columns = $schemaManager->listTableColumns($table);
            $foreignKeys = $schemaManager->listTableForeignKeys($table);

            $valueRequiredForeignKeys = [];
            foreach ($foreignKeys as $foreignKey) {
                $localColumns = array_map(function ($name) use ($columns) {
                    return $columns[$name];
                }, $foreignKey->getLocalColumns());

                if ($this->isValueRequiredAny($localColumns)) {
                    $valueRequiredForeignKeys[] = $foreignKey;
                }
            }
            $this->valueRequiredForeignKeysMap[$table] = $valueRequiredForeignKeys;
        }
        return $this->valueRequiredForeignKeysMap[$table];
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
