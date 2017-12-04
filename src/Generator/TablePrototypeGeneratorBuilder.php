<?php
namespace Lapaz\QuickBrownFox\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Faker\Generator as RandomValueGenerator;
use Lapaz\QuickBrownFox\Value\ColumnValueFactory;

class TablePrototypeGeneratorBuilder
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var RandomValueGenerator
     */
    protected $randomValueGenerator;

    /**
     * @var ColumnValueFactory
     */
    protected $columnValueFactory;

    /**
     * @param Connection $connection
     * @param RandomValueGenerator $randomValueGenerator
     */
    public function __construct(Connection $connection, RandomValueGenerator $randomValueGenerator)
    {
        $this->connection = $connection;
        $this->randomValueGenerator = $randomValueGenerator;
        $this->columnValueFactory = new ColumnValueFactory($connection, $randomValueGenerator);
    }

    /**
     * @param string $table
     * @return GeneratorInterface
     */
    public function build($table)
    {
        $schemaManager = $this->connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns($table);

        $generators = [];

        foreach ($columns as $column) {
            $name = $column->getName();
            if ($this->isValueRequiredFor($column)) {
                $generators[$name] = $this->columnValueFactory->createFor($column);
            }
        }

        return new ValueSetGenerator($generators);
    }

    /**
     * @param Column $column
     * @return bool
     */
    protected function isValueRequiredFor(Column $column)
    {
        return $column->getNotnull() && $column->getDefault() === null && !$column->getAutoincrement();
    }

    // TODO Add foreign key resolver
    // hasForeignKeyConstraint
}
