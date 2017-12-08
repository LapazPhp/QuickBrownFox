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
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     */
    public function resetCascading($table)
    {
        try {
            // TODO Allow custom reset strategy
            $restrictingTables = $this->findForeignRestrictingTables($table);
            foreach ($restrictingTables as $restrictingTable) {
                $this->resetCascading($restrictingTable);
            }
            $this->connection->executeUpdate("DELETE FROM " . $this->connection->quoteIdentifier($table));
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }

    /**
     * @param string $targetTableName
     * @return string[]
     */
    protected function findForeignRestrictingTables($targetTableName)
    {
        $restrictingTables = [];

        $tables = $this->connection->getSchemaManager()->listTables();
        foreach ($tables as $table) {
            $fks = $table->getForeignKeys();
            foreach ($fks as $fk) {
                if ($fk->getForeignTableName() === $targetTableName) {
                    $restrictingTables[] = $table->getName();
                }
            }
        }

        return $restrictingTables;
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
