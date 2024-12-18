<?php

namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TableCleanerTest extends TestCase
{
    private Connection $connection;

    private TableCleaner $tableCleaner;

    /**
     * @throws DBALException
     */
    public function testCleanFromTop()
    {
        $this->connection->insert('foo', ['val' => 'foo val']);
        $lastFooId = $this->connection->lastInsertId();
        $this->connection->insert('bar', ['val' => 'bar val', 'foo_id' => $lastFooId]);
        $lastBarId = $this->connection->lastInsertId();
        $this->connection->insert('baz', ['val' => 'baz val', 'bar_id' => $lastBarId]);
        $lastBazId = $this->connection->lastInsertId();
        $this->connection->update('foo', [
            'another_foo_id' => $lastFooId,
            'last_bar_id' => $lastBarId,
            'last_baz_id' => $lastBazId,
        ], ['id' => $lastFooId]);

        $this->tableCleaner->clean('foo');

        $c = $this->connection->executeQuery("SELECT COUNT(*) FROM foo")->fetchOne();
        $this->assertEquals(0, $c);
        $c = $this->connection->executeQuery("SELECT COUNT(*) FROM bar")->fetchOne();
        $this->assertEquals(0, $c);
        $c = $this->connection->executeQuery("SELECT COUNT(*) FROM baz")->fetchOne();
        $this->assertEquals(0, $c);
    }

    /**
     * @throws DBALException
     */
    public function testCleanBottom()
    {
        $this->connection->insert('foo', ['val' => 'foo val']);
        $lastFooId = $this->connection->lastInsertId();
        $this->connection->insert('bar', ['val' => 'bar val', 'foo_id' => $lastFooId]);
        $lastBarId = $this->connection->lastInsertId();
        $this->connection->insert('baz', ['val' => 'baz val', 'bar_id' => $lastBarId]);
        $lastBazId = $this->connection->lastInsertId();
        $this->connection->update('foo', [
            'another_foo_id' => $lastFooId,
            'last_bar_id' => $lastBarId,
            'last_baz_id' => $lastBazId,
        ], ['id' => $lastFooId]);

        $this->tableCleaner->clean('baz');

        $c = $this->connection->executeQuery("SELECT COUNT(*) FROM foo")->fetchOne();
        $this->assertEquals(0, $c);
        $c = $this->connection->executeQuery("SELECT COUNT(*) FROM bar")->fetchOne();
        $this->assertEquals(0, $c);
        $c = $this->connection->executeQuery("SELECT COUNT(*) FROM baz")->fetchOne();
        $this->assertEquals(0, $c);
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
            $this->setUpSchema();
        } catch (DBALException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $this->tableCleaner = new TableCleaner(
            $this->connection,
            new MetadataManager($this->connection)
        );

        parent::setUp();
    }

    /**
     * @throws DBALException
     */
    protected function setUpSchema(): void
    {
        $this->connection->executeStatement("PRAGMA foreign_keys=ON;");

        $this->connection->executeStatement("
            DROP TABLE IF EXISTS foo;
            DROP TABLE IF EXISTS bar;
            DROP TABLE IF EXISTS baz;
            CREATE TABLE foo (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              val VARCHAR(255) NOT NULL,
              another_foo_id INTEGER NULL,
              last_bar_id INTEGER NULL,
              last_baz_id INTEGER NULL
              ,
              FOREIGN KEY(another_foo_id) REFERENCES foo(id),
              FOREIGN KEY(last_bar_id) REFERENCES bar(id),
              FOREIGN KEY(last_baz_id) REFERENCES baz(id)
            );
            CREATE TABLE bar (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              val VARCHAR(255) NOT NULL,
              foo_id INTEGER NOT NULL
              ,
              FOREIGN KEY(foo_id) REFERENCES foo(id)
            );
            CREATE TABLE baz (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              val VARCHAR(255) NOT NULL,
              bar_id INTEGER NOT NULL
              ,
              FOREIGN KEY(bar_id) REFERENCES bar(id)
            );
        ");
    }
}
