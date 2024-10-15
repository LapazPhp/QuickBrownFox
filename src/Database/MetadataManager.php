<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\Type;

class MetadataManager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Type[][]
     */
    protected $tableColumnTypeMap;
    /**
     * @var string[][]
     */
    protected $referencingTablesMap;
    /**
     * @var string[][]
     */
    protected $invertReferencingTablesMap;
    /**
     * @var string[][]
     */
    protected $nullableForeignKeysMap;

    private ?AbstractSchemaManager $schemaManager = null;

    /**
     * MetadataManager constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->tableColumnTypeMap = [];
        $this->referencingTablesMap = null;
        $this->invertReferencingTablesMap = null;
        $this->nullableForeignKeysMap = [];
    }

    /**
     * @param string $targetTable
     * @return string[]
     */
    public function getReferencingTables($targetTable)
    {
        $this->analyzeForeignTableConstraint();

        if (isset($this->referencingTablesMap[$targetTable])) {
            return $this->referencingTablesMap[$targetTable];
        } else {
            return [];
        }
    }

    /**
     * @param string $targetTable
     * @return string[]
     */
    public function getInvertReferencingTables($targetTable)
    {
        $this->analyzeForeignTableConstraint();

        if (isset($this->invertReferencingTablesMap[$targetTable])) {
            return $this->invertReferencingTablesMap[$targetTable];
        } else {
            return [];
        }
    }

    private function getSchemaManager(): AbstractSchemaManager
    {
        if ($this->schemaManager === null) {
            $this->schemaManager = $this->connection->createSchemaManager();
        }
        return $this->schemaManager;
    }

    private function analyzeForeignTableConstraint()
    {
        if ($this->referencingTablesMap !== null && $this->invertReferencingTablesMap !== null) {
            return;
        }

        $this->referencingTablesMap = [];
        $this->invertReferencingTablesMap = [];
        $schemaManager = $this->getSchemaManager();
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
     * @return string[]
     */
    public function getNullableForeignKeys($targetTable)
    {
        if (isset($this->nullableForeignKeysMap[$targetTable])) {
            return $this->nullableForeignKeysMap[$targetTable];
        }

        $this->nullableForeignKeysMap[$targetTable] = [];
        $schemaManager = $this->getSchemaManager();

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

    private function isNullableColumnsAll(array $columnNames, $table)
    {
        $schemaManager = $this->getSchemaManager();
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
     * @return Type[]
     */
    public function getColumnTypes($table)
    {
        if (!isset($this->tableColumnTypeMap[$table])) {
            $schemaManager = $this->getSchemaManager();
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
