<?php

namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EasySessionManagerTest extends TestCase
{
    private Connection $connection;

    private EasySessionManager $manager;

    /**
     * @throws DBALException
     */
    public function testTruncate()
    {
        $session = $this->newSession();
        $session->reset('foo_parent');
        $session->reset('foo');

        $count = $this->connection->fetchOne("SELECT COUNT(*) FROM foo;");

        $this->assertEquals(0, $count);
    }

    /**
     * @throws DBALException
     */
    public function testInlineGenerate()
    {
        $session = $this->newSession();
        $session->into('foo')->generate(10);

        $count = $this->connection->fetchOne("SELECT COUNT(*) FROM foo;");

        $this->assertEquals(10, $count);
    }

    /**
     * @throws DBALException
     */
    public function testInlineFixedArrayFixture()
    {
        $session = $this->newSession();
        $session->into('foo')->load([
            [
                'number1' => 1,
                'number2' => 2,
            ],
            [
                'number2' => 2,
                'number3' => 3,
            ],
            [
                'number3' => 3,
            ],
        ]);

        $rows = $this->connection->fetchAllAssociative("SELECT * FROM foo;");
        $this->assertCount(3, $rows);

        $this->assertEquals(1, $rows[0]['number1']);
        $this->assertEquals(2, $rows[0]['number2']);

        $this->assertEquals(2, $rows[1]['number2']);
        $this->assertEquals(3, $rows[1]['number3']);

        $this->assertEquals(3, $rows[2]['number3']);
    }

    /**
     * @throws DBALException
     */
    public function testIsolateDifferentSession()
    {
        $session = $this->newSession();
        $session->into('foo')->generate(10);

        $session = $this->newSession();
        $session->into('foo')->generate(2);

        $rows = $this->connection->fetchAllAssociative("SELECT * FROM foo;");
        $this->assertCount(2, $rows);
    }

    /**
     * @throws DBALException
     */
    public function testExistingForeignRecord()
    {
        $session = $this->newSession();

        $session->into('foo_parent')->generate(2);

        $session->into('foo')->generate(3);

        $rows = $this->connection->fetchAllAssociative("SELECT * FROM foo;");
        $this->assertEquals(1, $rows[0]['parent_id']);
        $this->assertEquals(2, $rows[1]['parent_id']);
        $this->assertEquals(1, $rows[2]['parent_id']);
    }

    protected function newSession(): FixtureSetupSessionInterface
    {
        return $this->manager->newSession();
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

        $this->manager = new EasySessionManager($this->connection);

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
            DROP TABLE IF EXISTS foo_parent;
            CREATE TABLE foo_parent (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              string1 VARCHAR(255) NOT NULL
            );
            CREATE TABLE foo (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              parent_id INTEGER NOT NULL,
              number1 INTEGER(11) NOT NULL,
              number2 INTEGER(11) NOT NULL DEFAULT 1,
              number3 INTEGER(11) NULL,
              float1 FLOAT NOT NULL,
              float2 FLOAT NOT NULL DEFAULT 1,
              float3 FLOAT NULL,
              decimal1 DECIMAL(3,1) NOT NULL,
              decimal2 DECIMAL(3,1) NOT NULL DEFAULT 1,
              decimal3 DECIMAL(3,1) NULL,
              chars1 CHAR(8) NOT NULL,
              chars2 CHAR(8) NOT NULL DEFAULT '',
              chars3 CHAR(8) NULL,
              string1 VARCHAR(255) NOT NULL,
              string2 VARCHAR(255) NOT NULL DEFAULT '',
              string3 VARCHAR(255) NULL,
              short_string1 VARCHAR(4) NOT NULL,
              text1 TEXT NOT NULL,
              text2 TEXT NOT NULL DEFAULT '',
              text3 TEXT NULL,
              bool1 TINYINT(1) NOT NULL,
              bool2 TINYINT(1) NOT NULL DEFAULT 0,
              bool3 TINYINT(1) NULL,
              date1 DATE NOT NULL,
              time1 TIME NOT NULL,
              datetime1 DATETIME NOT NULL
              ,
              FOREIGN KEY(parent_id) REFERENCES foo_parent(id)
            );
        ");
    }
}
