<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Lapaz\QuickBrownFox\Exception\DatabaseException;

class Loader
{
    /**
     * @var MetadataManager
     */
    protected MetadataManager $metadataManager;

    /**
     * @param Connection $connection
     */
    public function __construct(
        protected Connection $connection
    )
    {
        $this->metadataManager = new MetadataManager($this->connection);
    }

    /**
     * @param string $table
     */
    public function resetCascading(string $table): void
    {
        $cleaner = new TableCleaner($this->connection, $this->metadataManager);
        $cleaner->clean($table);
    }

    /**
     * @param string $table
     * @param list<array<string,mixed>> $records
     * @return list<int|string|false>
     */
    public function load(string $table, array $records): array
    {
        try {
            $columnTypes = $this->metadataManager->getColumnTypes($table);
            $primaryKeys = [];
            foreach ($records as $record) {
                $types = [];
                foreach (array_keys($record) as $column) {
                    $types[$column] = $columnTypes[$column];
                }

                list($record, $types) = $this->remapBooleanToInt($record, $types);

                $affectedRows = $this->connection->insert($table, $record, $types);
                if ($affectedRows < 1) {
                    throw new DatabaseException('INSERT was sent but actually no rows affected');
                }
                // More than 2 rows may be affected successfully by DB trigger.

                try {
                    $primaryKeys[] = $this->connection->lastInsertId();
                    // FIXME Check when single ID not presented (UUID, complex pk or such as)
                } catch (DBALException) {
                    $primaryKeys[] = false;
                }
            }
            return $primaryKeys;
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }

    private function remapBooleanToInt(array $record, array $types): array
    {
        foreach (array_keys($types) as $column) {
            if ($types[$column] === Types::BOOLEAN) {
                $toBeConverted = true;
                $types[$column] = Types::INTEGER;
            } elseif ($types[$column] === ParameterType::BOOLEAN) {
                $toBeConverted = true;
                $types[$column] = ParameterType::INTEGER;
            } elseif ($types[$column] instanceof BooleanType) {
                $toBeConverted = true;
                try {
                    $types[$column] = Type::getTypeRegistry()->get(Types::INTEGER);
                } catch (DBALException $e) {
                    throw DatabaseException::fromDBALException($e);
                }
            } else {
                $toBeConverted = false;
            }
            if ($toBeConverted && isset($record[$column])) {
                // Ensure integer value if the field is not NULL.
                $record[$column] = intval($record[$column]);
            }
        }

        return [$record, $types];
    }
}
