<?php

namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Lapaz\QuickBrownFox\Exception\DatabaseException;

class TableCleaner
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
     * @var bool[]
     */
    private $finishedTables;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection, MetadataManager $metadataManager)
    {
        $this->connection = $connection;
        $this->metadataManager = $metadataManager;
        $this->finishedTables = [];
    }

    /**
     * @param string $table
     */
    public function clean($table)
    {
        $invertReferencingTables = $this->metadataManager->getInvertReferencingTables($table);

        foreach ($invertReferencingTables as $referencingTable) {
            $clearableForeignColumns = $this->metadataManager->getNullableForeignKeys($referencingTable);
            if (!empty($clearableForeignColumns)) {
                $set = [];
                foreach ($clearableForeignColumns as $clearableForeignColumn) {
                    $set[] = $this->connection->quoteIdentifier($clearableForeignColumn) . " = NULL";
                }
                try {
                    $this->connection->executeStatement(
                        "UPDATE " . $this->connection->quoteIdentifier($referencingTable) .
                        " SET " . implode(", ", $set)
                    );
                } catch (DBALException $e) {
                    throw DatabaseException::fromDBALException($e);
                }
            }
        }

        foreach ($this->metadataManager->getReferencingTables($table) as $referencingTable) {
            if (!isset($this->finishedTables[$referencingTable])) {
                $this->clean($referencingTable);
            }
        }

        try {
            $this->connection->executeStatement("DELETE FROM " . $this->connection->quoteIdentifier($table));
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
        $this->finishedTables[$table] = true;

        foreach ($invertReferencingTables as $referencingTable) {
            if (!isset($this->finishedTables[$referencingTable])) {
                $this->clean($referencingTable);
            }
        }
    }
}