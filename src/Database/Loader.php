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
    public function unload($table)
    {
        try {
            $sql = $this->connection->getDatabasePlatform()->getTruncateTableSQL($table);
            $this->connection->exec($sql);
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
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
