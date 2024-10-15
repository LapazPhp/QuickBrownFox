<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\Type;

class MetadataManager
{
    /**
     * @var array<string,array<string,Type>>
     */
    protected array $tableColumnTypeMap;

    /**
     * @var array<string,list<string>>|null
     */
    protected ?array $referencingTablesMap;

    /**
     * @var array<string,list<string>>|null
     */
    protected ?array $invertReferencingTablesMap;

    /**
     * @var array<string,list<string>>
     */
    protected array $nullableForeignKeysMap;

    private ?AbstractSchemaManager $schemaManager = null;

    /**
     * MetadataManager constructor.
     * @param Connection $connection
     */
    public function __construct(
        protected Connection $connection
    )
    {
        $this->tableColumnTypeMap = [];
        $this->referencingTablesMap = null;
        $this->invertReferencingTablesMap = null;
        $this->nullableForeignKeysMap = [];
    }

    /**
     * @param string $targetTable
     * @return list<string>
     */
    public function getReferencingTables(string $targetTable): array
    {
        $this->analyzeForeignTableConstraint();
        return $this->referencingTablesMap[$targetTable] ?? [];
    }

    /**
     * @param string $targetTable
     * @return list<string>
     */
    public function getInvertReferencingTables(string $targetTable): array
    {
        $this->analyzeForeignTableConstraint();
        return $this->invertReferencingTablesMap[$targetTable] ?? [];
    }

    private function analyzeForeignTableConstraint(): void
    {
        if ($this->referencingTablesMap !== null && $this->invertReferencingTablesMap !== null) {
            return;
        }

        $this->referencingTablesMap = [];
        $this->invertReferencingTablesMap = [];
        $schemaManager = new SchemaManagerProxy($this->connection);
        $tables = $schemaManager->listTableNames();
        foreach ($tables as $referencingTableName) {
            $fks = $schemaManager->listTableForeignKeys($referencingTableName);
            foreach ($fks as $fk) {
                $foreignTableName = $fk->getForeignTableName();
                $localColumnNames = $fk->getLocalColumns();

                if (
                    !$this->isNullableColumnsAll($localColumnNames, $referencingTableName) &&
                    $referencingTableName !== $foreignTableName
                ) {
                    if (!(
                        isset($this->referencingTablesMap[$foreignTableName]) &&
                        in_array($referencingTableName, $this->referencingTablesMap[$foreignTableName])
                    )) {
                        $this->referencingTablesMap[$foreignTableName][] = $referencingTableName;
                    }
                } else {
                    if (!(
                        isset($this->invertReferencingTablesMap[$foreignTableName]) &&
                        in_array($referencingTableName, $this->invertReferencingTablesMap[$foreignTableName])
                    )) {
                        $this->invertReferencingTablesMap[$foreignTableName][] = $referencingTableName;
                    }
                }
            }
        }
    }

    /**
     * @param string $targetTable
     * @return list<string>
     */
    public function getNullableForeignKeys(string $targetTable): array
    {
        if (isset($this->nullableForeignKeysMap[$targetTable])) {
            return $this->nullableForeignKeysMap[$targetTable];
        }

        $this->nullableForeignKeysMap[$targetTable] = [];
        $schemaManager = new SchemaManagerProxy($this->connection);

        $fks = $schemaManager->listTableForeignKeys($targetTable);
        foreach ($fks as $fk) {
            $localColumnNames = $fk->getLocalColumns();
            if ($this->isNullableColumnsAll($localColumnNames, $targetTable)) {
                foreach ($localColumnNames as $localColumnName) {
                    if (!in_array($localColumnName, $this->nullableForeignKeysMap[$targetTable])) {
                        $this->nullableForeignKeysMap[$targetTable][] = $localColumnName;
                    }
                }
            }
        }

        return $this->nullableForeignKeysMap[$targetTable];
    }

    private function isNullableColumnsAll(array $columnNames, $table): bool
    {
        $schemaManager = new SchemaManagerProxy($this->connection);
        $columns = $schemaManager->listTableColumns($table);
        foreach ($columns as $column) {
            $name = $column->getName();
            if (in_array($name, $columnNames) && $column->getNotnull()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $table
     * @return array<string,Type>
     */
    public function getColumnTypes(string $table): array
    {
        if (!isset($this->tableColumnTypeMap[$table])) {
            $schemaManager = new SchemaManagerProxy($this->connection);
            $columns = $schemaManager->listTableColumns($table);
            $types = [];
            foreach ($columns as $column) {
                $name = $column->getName();
                $types[$name] = $column->getType();
            }
            $this->tableColumnTypeMap[$table] = $types;
        }

        return $this->tableColumnTypeMap[$table];
    }
}
