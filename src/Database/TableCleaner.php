<?php

namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Lapaz\QuickBrownFox\Exception\DatabaseException;

class TableCleaner
{
    /**
     * @var array<string,bool>
     */
    private array $finishedTables;

    /**
     * @param Connection $connection
     * @param MetadataManager $metadataManager
     */
    public function __construct(
        protected Connection $connection,
        protected MetadataManager $metadataManager
    )
    {
        $this->finishedTables = [];
    }

    /**
     * @param string $table
     */
    public function clean(string $table): void
    {
        try {
            $invertReferencingTables = $this->metadataManager->getInvertReferencingTables($table);

            foreach ($invertReferencingTables as $referencingTable) {
                $clearableForeignColumns = $this->metadataManager->getNullableForeignKeys($referencingTable);
                if (!empty($clearableForeignColumns)) {
                    $set = [];
                    foreach ($clearableForeignColumns as $clearableForeignColumn) {
                        $set[] = $this->connection->quoteIdentifier($clearableForeignColumn) . " = NULL";
                    }
                    $this->connection->executeStatement(
                        "UPDATE " . $this->connection->quoteIdentifier($referencingTable) .
                        " SET " . implode(", ", $set)
                    );
                }
            }

            foreach ($this->metadataManager->getReferencingTables($table) as $referencingTable) {
                if (!isset($this->finishedTables[$referencingTable])) {
                    $this->clean($referencingTable);
                }
            }

            $this->connection->executeStatement("DELETE FROM " . $this->connection->quoteIdentifier($table));
            $this->finishedTables[$table] = true;

            foreach ($invertReferencingTables as $referencingTable) {
                if (!isset($this->finishedTables[$referencingTable])) {
                    $this->clean($referencingTable);
                }
            }
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }
}