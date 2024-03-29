<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Exception as DBALException;
use Lapaz\QuickBrownFox\Value\ForeignKeyReferencedValue;
use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class ForeignTableFetcher implements ValueProviderInterface
{
    const FOREIGN_REFERENCE_LIMIT = 10;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array key=local-column, value=foreign-column
     */
    protected $mapping;

    /**
     * @var array
     */
    protected $foreignRecords;

    /**
     * @var TablePrototypeGeneratorBuilder
     */
    protected $prototypeBuilder;

    /**
     * @param string $table
     * @param array $mapping
     * @param TablePrototypeGeneratorBuilder $prototypeBuilder
     */
    public function __construct($table, $mapping, TablePrototypeGeneratorBuilder $prototypeBuilder)
    {
        $this->table = $table;
        $this->mapping = $mapping;
        $this->prototypeBuilder = $prototypeBuilder;

        $this->foreignRecords = null;
    }

    /**
     * @return ForeignKeyReferencedValue[]
     */
    public function createValueProviders()
    {
        $valueProviders = [];
        foreach ($this->mapping as $local => $foreignColumn) {
            $valueProviders[$local] = new ForeignKeyReferencedValue($this, $foreignColumn);
        }
        return $valueProviders;
    }

    /**
     * @param int $index
     * @return array
     * @throws DBALException
     */
    public function getAt($index)
    {
        $this->ensureForeignRecords(static::FOREIGN_REFERENCE_LIMIT);
        return $this->foreignRecords[$index % count($this->foreignRecords)];
    }

    /**
     * @param int $maxAmount
     * @throws DBALException
     */
    private function ensureForeignRecords($maxAmount)
    {
        if (!empty($this->foreignRecords)) {
            return;
        }

        $connection = $this->prototypeBuilder->getConnection();

        $fields = implode(", ", array_map(function ($name) use ($connection) {
            return $connection->quoteIdentifier($name);
        }, array_values($this->mapping)));

        $table = $connection->quoteIdentifier($this->table);

        $this->foreignRecords = $connection->fetchAllAssociative("SELECT {$fields} FROM {$table} LIMIT {$maxAmount}");

        if (!empty($this->foreignRecords)) {
            return;
        }

        $prototypeGenerator = $this->prototypeBuilder->build($this->table);
        $record = $prototypeGenerator->generateAt(0);

        $loader = new Loader($connection);
        $loader->load($this->table, [$record]);

        $this->foreignRecords = $connection->fetchAllAssociative("SELECT {$fields} FROM {$table} LIMIT {$maxAmount}");
    }
}
