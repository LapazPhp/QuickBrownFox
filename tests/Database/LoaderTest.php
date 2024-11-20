<?php

namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LoaderTest extends TestCase
{
    private Connection $connection;

    /**
     * @throws DBALException
     */
    public function testLoadForNonBooleanTinyInt()
    {
        $this->connection->executeStatement("
            DROP TABLE IF EXISTS foo;
            CREATE TABLE foo (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              bool_tinyint TINYINT(1) NOT NULL,
              non_bool_tinyint TINYINT(2) NOT NULL
            );
        ");

        $loader = new Loader($this->connection);

        $loader->load('foo', [
            ['id' => 1, 'bool_tinyint' => false, 'non_bool_tinyint' => 0],
            ['id' => 2, 'bool_tinyint' => true, 'non_bool_tinyint' => 1],
            ['id' => 3, 'bool_tinyint' => true, 'non_bool_tinyint' => 2],
        ]);

        $result = $this->connection->executeQuery("SELECT * FROM foo ORDER BY id ASC;");
        $rows = $result->fetchAllNumeric();

        $this->assertCount(3, $rows);
        $this->assertEquals([1, 0, 0], $rows[0]);
        $this->assertEquals([2, 1, 1], $rows[1]);
        $this->assertEquals([3, 1, 2], $rows[2]);
    }

    protected function setUp(): void
    {
        $url = 'sqlite:::memory:';
        // $url = 'sqlite:///' . realpath(__DIR__ . '/..') . '/loader-test.sqlite';

        try {
            $this->connection = DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'url' => $url,
            ]);
        } catch (DBALException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        parent::setUp();
    }
}
