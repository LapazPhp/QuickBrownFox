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
     * @var MetadataManager
     */
    protected $metadataManager;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->metadataManager = new MetadataManager($this->connection);
    }

    /**
     * @param string $table
     */
    public function resetCascading($table)
    {
        foreach ($this->metadataManager->getReferencingTables($table) as $referencingTable) {
            $this->resetCascading($referencingTable);
        }
        // TODO Allow custom reset strategy

        try {
            $this->connection->executeUpdate("DELETE FROM " . $this->connection->quoteIdentifier($table));
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
        $columnTypes = $this->metadataManager->getColumnTypes($table);
        $primaryKeys = [];
        foreach ($records as $record) {
            $types = array_map(function ($column) use ($columnTypes) {
                return $columnTypes[$column];
            }, array_keys($record));

            try {
                $this->connection->insert($table, $record, $types);
            } catch (DBALException $e) {
                throw DatabaseException::fromDBALException($e);
            }
            $primaryKeys[] = $this->connection->lastInsertId();
            // FIXME Check when single ID not presented (UUID, complex pk or such as)
        }
        return $primaryKeys;
    }
}
