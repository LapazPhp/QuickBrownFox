<?php

namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Lapaz\QuickBrownFox\Exception\DatabaseException;

class SchemaManagerProxy
{
    private AbstractSchemaManager $schemaManager;

    public function __construct(
        Connection $connection,
    )
    {
        try {
            $this->schemaManager = $connection->createSchemaManager();
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }

    /**
     * @return array<string>
     */
    public function listTableNames(): array
    {
        try {
            return $this->schemaManager->listTableNames();
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }

    /**
     * @param string $table
     * @return array<string, Column>
     */
    public function listTableColumns(string $table): array
    {
        try {
            return $this->schemaManager->listTableColumns($table);
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }

    /**
     * @param string $table
     * @return array<int|string, ForeignKeyConstraint>
     */
    public function listTableForeignKeys(string $table): array
    {
        try {
            return $this->schemaManager->listTableForeignKeys($table);
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }
}