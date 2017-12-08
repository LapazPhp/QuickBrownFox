<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Lapaz\QuickBrownFox\Exception\DatabaseException;

class Loader
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string[][]
     */
    protected $referencingTablesMap;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        $this->referencingTablesMap = null;
    }

    /**
     * @param string $table
     */
    public function resetCascading($table)
    {
        try {
            // TODO Allow custom reset strategy
            $this->ensureReferencingForeignTablesMap();
            if (isset($this->referencingTablesMap[$table])) {
                foreach ($this->referencingTablesMap[$table] as $restrictingTable) {
                    $this->resetCascading($restrictingTable);
                }
            }
            $this->connection->executeUpdate("DELETE FROM " . $this->connection->quoteIdentifier($table));
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }

    /**
     *
     */
    protected function ensureReferencingForeignTablesMap()
    {
        if ($this->referencingTablesMap !== null) {
            return;
        }

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

    /**
     * @param string $table
     * @param array $records
     * @return array
     */
    public function load($table, array $records)
    {
        $primaryKeys = [];
        foreach ($records as $record) {
            $this->connection->insert(
                $table,
                $record,
                $this->fieldTypes($table, array_keys($record))
            );
            $primaryKeys[] = $this->connection->lastInsertId();
        }
        return $primaryKeys;
    }

    /**
     * @param string $table
     * @param string[] $fields
     * @return string[]
     */
    private function fieldTypes($table, $fields)
    {
        $schemaManager = $this->connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns($table);
        $types = [];
        foreach ($columns as $column) {
            $name = $column->getName();
            if (in_array($name, $fields)) {
                $types[$name] = $column->getType()->getName();
            }
        }
        return $types;
    }
}
