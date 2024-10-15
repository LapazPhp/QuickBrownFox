<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Lapaz\QuickBrownFox\Generator\GeneratorComposite;
use Lapaz\QuickBrownFox\Generator\GeneratorInterface;
use Lapaz\QuickBrownFox\Generator\ValueSetGenerator;
use Lapaz\QuickBrownFox\Value\ColumnValueFactory;
use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class TablePrototypeGeneratorBuilder
{
    /**
     * @var ColumnValueFactory
     */
    protected ColumnValueFactory $columnValueFactory;

    /**
     * @var array<string,list<Column>>
     */
    protected array $valueRequiredColumnsMap;

    /**
     * @var array<string,list<ForeignKeyConstraint>>
     */
    protected array $valueRequiredForeignKeysMap;

    private ?AbstractSchemaManager $schemaManager = null;

    /**
     * @param Connection $connection
     * @param RepositoryAggregateInterface $repositoryAggregate
     */
    public function __construct(
        protected Connection $connection,
        protected RepositoryAggregateInterface $repositoryAggregate
    )
    {
        $this->columnValueFactory = new ColumnValueFactory(
            $connection,
            $repositoryAggregate->getRandomValueGenerator()
        );
        $this->valueRequiredColumnsMap = [];
        $this->valueRequiredForeignKeysMap = [];
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @throws DBALException
     */
    private function getSchemaManager(): AbstractSchemaManager
    {
        if ($this->schemaManager === null) {
            $this->schemaManager = $this->connection->createSchemaManager();
        }
        return $this->schemaManager;
    }

    /**
     * @param string $table
     * @return GeneratorInterface
     * @throws DBALException
     */
    public function build(string $table): GeneratorInterface
    {
        $prototypeGenerator = new ValueSetGenerator(array_merge(
            $this->normalColumnValueProviders($table),
            $this->foreignKeyColumnValueProviders($table)
        ));

        $generatorRepository = $this->repositoryAggregate->getGeneratorRepositoryFor($table);
        $tableDefaultsGenerator = $generatorRepository->getTableDefaults();
        if ($tableDefaultsGenerator) {
            $prototypeGenerator = new GeneratorComposite([
                $prototypeGenerator,
                $tableDefaultsGenerator,
            ]);
        }

        return $prototypeGenerator;
    }

    /**
     * @param string $table
     * @return array<string,ValueProviderInterface>
     * @throws DBALException
     */
    protected function normalColumnValueProviders(string $table): array
    {
        $valueProviders = [];
        foreach ($this->valueRequiredColumns($table) as $column) {
            $valueProviders[$column->getName()] = $this->columnValueFactory->createFor($column);
        }
        return $valueProviders;
    }

    /**
     * @param string $table
     * @return list<Column>
     * @throws DBALException
     */
    protected function valueRequiredColumns(string $table): array
    {
        if (!isset($this->valueRequiredColumnsMap[$table])) {
            $schemaManager = $this->getSchemaManager();
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
    protected function isValueRequiredFor(Column $column): bool
    {
        return $column->getNotnull() && $column->getDefault() === null && !$column->getAutoincrement();
    }

    /**
     * @param string $table
     * @return array<string,ValueProviderInterface>
     * @throws DBALException
     */
    protected function foreignKeyColumnValueProviders(string $table): array
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
     * @return list<ForeignKeyConstraint>
     * @throws DBALException
     */
    protected function valueRequiredForeignKeys(string $table): array
    {
        if (!isset($this->valueRequiredForeignKeysMap[$table])) {
            $schemaManager = $this->getSchemaManager();
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
    protected function isValueRequiredAny(array $columns): bool
    {
        foreach ($columns as $column) {
            if ($this->isValueRequiredFor($column)) {
                return true;
            }
        }
        return false;
    }
}
