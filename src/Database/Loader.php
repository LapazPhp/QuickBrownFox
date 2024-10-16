<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as PDOMySQLDriver;
use Doctrine\DBAL\Exception as DBALException;
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

                // Avoid PDO MySQL bug
                list($record, $types) = $this->phpBug38546RemapBooleanToIntForPDOMySQL($record, $types);

                $affectedRows = $this->connection->insert($table, $record, $types);
                if ($affectedRows < 1) {
                    throw new DatabaseException('INSERT was sent but actually no rows affected');
                }
                // More than 2 rows may be affected successfully by DB trigger.

                $primaryKeys[] = $this->connection->lastInsertId();
                // FIXME Check when single ID not presented (UUID, complex pk or such as)
            }
            return $primaryKeys;
        } catch (DBALException $e) {
            throw DatabaseException::fromDBALException($e);
        }
    }

    /**
     * Workaround: This method replaces BOOL to INT for MySQL prepared statement.
     * PDO's type system has a bug around boolean when MySQL ATTR_EMULATE_PREPARES off.
     * If \PDO::PARAM_BOOL specified for INSERT or UPDATE, the SQL would be ignored.
     *
     * https://bugs.php.net/bug.php?id=38546
     *
     * @param array $record
     * @param array $types
     * @return array
     */
    private function phpBug38546RemapBooleanToIntForPDOMySQL(array $record, array $types): array
    {
        if ($this->connection->getDriver() instanceof PDOMySQLDriver) {
            return [$record, $types];
        }

        foreach (array_keys($types) as $column) {
            if ($types[$column] === Types::BOOLEAN) {
                $types[$column] = Types::INTEGER;
                if (isset($record[$column])) {
                    // Ensure integer value if the field is not NULL.
                    $record[$column] = intval($record[$column]);
                }
            }
        }

        return [$record, $types];
    }
}
