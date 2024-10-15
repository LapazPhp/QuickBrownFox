<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Exception as DBALException;
use Lapaz\QuickBrownFox\Value\ForeignKeyReferencedValue;
use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class ForeignTableFetcher implements ValueProviderInterface
{
    const FOREIGN_REFERENCE_LIMIT = 10;

    /**
     * @var array|null
     */
    protected ?array $foreignRecords;

    /**
     * @param string $table
     * @param array<string,mixed> $mapping key=local-column, value=foreign-column
     * @param TablePrototypeGeneratorBuilder $prototypeBuilder
     */
    public function __construct(
        protected string $table,
        protected array $mapping,
        protected TablePrototypeGeneratorBuilder $prototypeBuilder
    )
    {
        $this->foreignRecords = null;
    }

    /**
     * @return array<string,ForeignKeyReferencedValue>
     */
    public function createValueProviders(): array
    {
        $valueProviders = [];
        foreach ($this->mapping as $local => $foreignColumn) {
            $valueProviders[$local] = new ForeignKeyReferencedValue($this, $foreignColumn);
        }
        return $valueProviders;
    }

    /**
     * @param int $index
     * @return mixed
     * @throws DBALException
     */
    public function getAt(int $index): mixed
    {
        $this->ensureForeignRecords(static::FOREIGN_REFERENCE_LIMIT);
        return $this->foreignRecords[$index % count($this->foreignRecords)];
    }

    /**
     * @param int $maxAmount
     * @throws DBALException
     * @noinspection PhpSameParameterValueInspection
     */
    private function ensureForeignRecords(int $maxAmount): void
    {
        if (!empty($this->foreignRecords)) {
            return;
        }

        $connection = $this->prototypeBuilder->getConnection();

        $fields = implode(", ", array_map(function ($name) use ($connection) {
            return $connection->quoteIdentifier($name);
        }, array_values($this->mapping)));

        $table = $connection->quoteIdentifier($this->table);

        $this->foreignRecords = $connection->fetchAllAssociative("SELECT $fields FROM $table LIMIT $maxAmount");

        if (!empty($this->foreignRecords)) {
            return;
        }

        $prototypeGenerator = $this->prototypeBuilder->build($this->table);
        $record = $prototypeGenerator->generateAt(0);

        $loader = new Loader($connection);
        $loader->load($this->table, [$record]);

        $this->foreignRecords = $connection->fetchAllAssociative("SELECT $fields FROM $table LIMIT $maxAmount");
    }
}
