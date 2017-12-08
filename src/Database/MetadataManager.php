<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;

class MetadataManager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string[][]
     */
    protected $tableColumnTypeMap;
    /**
     * @var string[][]
     */
    protected $referencingTablesMap;

    /**
     * MetadataManager constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->tableColumnTypeMap = [];
        $this->referencingTablesMap = null;
    }

    /**
     * @param string $targetTable
     * @return string[]
     */
    public function getReferencingTables($targetTable)
    {
        if ($this->referencingTablesMap === null) {
            $this->referencingTablesMap = [];
            $schemaManager = $this->connection->getSchemaManager();
            $tables = $schemaManager->listTableNames();
            foreach ($tables as $referencingTableName) {
                $fks = $schemaManager->listTableForeignKeys($referencingTableName);
                foreach ($fks as $fk) {
                    $foreignTableName = $fk->getForeignTableName();
                    if (
                        $referencingTableName !== $foreignTableName &&
                        !(
                            isset($this->referencingTablesMap[$foreignTableName]) &&
                            in_array($referencingTableName, $this->referencingTablesMap[$foreignTableName])
                        )
                    ) {
                        $this->referencingTablesMap[$foreignTableName][] = $referencingTableName;
                    }
                }
            }
        }

        if (isset($this->referencingTablesMap[$targetTable])) {
            return $this->referencingTablesMap[$targetTable];
        } else {
            return [];
        }
    }

    /**
     * @param string $table
     * @return string[]
     */
    public function getColumnTypes($table)
    {
        if (!isset($this->tableColumnTypeMap[$table])) {
            $schemaManager = $this->connection->getSchemaManager();
            $columns = $schemaManager->listTableColumns($table);
            $types = [];
            foreach ($columns as $column) {
                $name = $column->getName();
                $types[$name] = $column->getType()->getName();
            }
            $this->tableColumnTypeMap[$table] = $types;
        }

        return $this->tableColumnTypeMap[$table];
    }
}
