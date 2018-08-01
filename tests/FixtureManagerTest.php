<?php
namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\SQLLogger;
use Lapaz\QuickBrownFox\Context\TableDefinition;
use PHPUnit\Framework\TestCase;

class FixtureManagerTest extends TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var SQLLogger
     */
    protected $sqlLogger;

    /**
     * @var FixtureManager
     */
    protected $manager;

    /**
     * @throws DBALException
     */
    public function testTruncate()
    {
        $session = $this->newSession();
        $session->reset('foo_parent');
        $session->reset('foo');

        $count = $this->connection->fetchColumn("SELECT COUNT(*) FROM foo;");

        $this->assertEquals(0, $count);
    }

    /**
     * @throws DBALException
     */
    public function testInlineGenerate()
    {
        $session = $this->newSession();
        $session->into('foo')->generate(10);

        $count = $this->connection->fetchColumn("SELECT COUNT(*) FROM foo;");

        $this->assertEquals(10, $count);
    }

    public function testDefaultGenerator()
    {
        $this->manager->table('foo', function (TableDefinition $td) {
            $td->defaults()->define([
                'number2' => 2,
                'number3' => 3,
            ]);
        });

        $session = $this->newSession();
        $session->into('foo')->generate();

        $rows = $this->connection->fetchAll("SELECT * FROM foo;");

        $this->assertCount(1, $rows);

        $this->assertEquals(2, $rows[0]['number2']);
        $this->assertEquals(3, $rows[0]['number3']);
    }

    public function testPredefinedGenerator()
    {
        $this->manager->table('foo', function (TableDefinition $td) {
            $td->defaults()->define([
                'number2' => 2,
                'number3' => 3
            ]);
            $td->generator('10x')->define([
                'number3' => 30,
            ]);
        });

        $session = $this->newSession();
        $session->into('foo')->generate();
        $session->into('foo')->with('10x')->generate();
        $session->into('foo')->with([
            'number3' => 300,
        ])->generate();

        $rows = $this->connection->fetchAll("SELECT * FROM foo ORDER BY id;");

        $this->assertEquals(2, $rows[0]['number2']);
        $this->assertEquals(3, $rows[0]['number3']);

        $this->assertEquals(2, $rows[1]['number2']);
        $this->assertEquals(30, $rows[1]['number3']);

        $this->assertEquals(2, $rows[2]['number2']);
        $this->assertEquals(300, $rows[2]['number3']);
    }

    public function testInlineCallbackGenerator()
    {
        $this->manager->table('foo', function (TableDefinition $td) {
            $td->defaults()->define([
                'number2' => 2,
                'number3' => 3
            ]);
        });

        $session = $this->newSession();
        $session->into('foo')->with([
            'number3' => function ($i) {
                return $i;
            }
        ])->generate(10, 10);

        $rows = $this->connection->fetchAll("SELECT * FROM foo;");

        $this->assertCount(10, $rows);

        $this->assertEquals(2, $rows[0]['number2']);
        $this->assertEquals(2, $rows[1]['number2']);
        $this->assertEquals(2, $rows[9]['number2']);

        $this->assertEquals(10, $rows[0]['number3']);
        $this->assertEquals(11, $rows[1]['number3']);
        $this->assertEquals(19, $rows[9]['number3']);
    }

    public function testPredefinedGeneratorComposition()
    {
        $this->manager->table('foo', function (TableDefinition $td) {
            $td->generator('num2')->define([
                'number2' => 2,
            ]);
            $td->generator('num3')->define([
                'number3' => 3,
            ]);
            $td->generator('num23')->with(['num2', 'num3'])->define();
            $td->generator('num23x10')->with('num23')->define([
                'number3' => 30,
            ]);
        });

        $session = $this->newSession();
        $session->into('foo')->with('num23')->generate();
        $session->into('foo')->with('num23x10')->generate();

        $rows = $this->connection->fetchAll("SELECT * FROM foo;");

        $this->assertEquals(2, $rows[0]['number2']);
        $this->assertEquals(3, $rows[0]['number3']);

        $this->assertEquals(2, $rows[1]['number2']);
        $this->assertEquals(30, $rows[1]['number3']);
    }

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

        $rows = $this->connection->fetchAll("SELECT * FROM foo;");
        $this->assertCount(3, $rows);

        $this->assertEquals(1, $rows[0]['number1']);
        $this->assertEquals(2, $rows[0]['number2']);

        $this->assertEquals(2, $rows[1]['number2']);
        $this->assertEquals(3, $rows[1]['number3']);

        $this->assertEquals(3, $rows[2]['number3']);
    }

    public function testPredefinedFixture()
    {
        $this->manager->table('foo', function (TableDefinition $td) {
            $td->fixture('3rec')->define([
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
        });

        $session = $this->newSession();
        $session->into('foo')->load('3rec');

        $rows = $this->connection->fetchAll("SELECT * FROM foo;");
        $this->assertCount(3, $rows);

        $this->assertEquals(1, $rows[0]['number1']);
        $this->assertEquals(2, $rows[0]['number2']);

        $this->assertEquals(2, $rows[1]['number2']);
        $this->assertEquals(3, $rows[1]['number3']);

        $this->assertEquals(3, $rows[2]['number3']);
    }

    public function testPredefinedGeneratedFixture()
    {
        $this->manager->table('foo', function (TableDefinition $td) {
            $td->fixture('3rec-num2seq')->defineGenerated(function ($i) {
                return [
                    'number2' => $i,
                ];
            }, 3, 10);
        });

        $session = $this->newSession();
        $session->into('foo')->load('3rec-num2seq');

        $rows = $this->connection->fetchAll("SELECT * FROM foo;");
        $this->assertCount(3, $rows);

        $this->assertEquals(10, $rows[0]['number2']);
        $this->assertEquals(11, $rows[1]['number2']);
        $this->assertEquals(12, $rows[2]['number2']);
    }

    public function testIsolateDifferentSession()
    {
        $session = $this->newSession();
        $session->into('foo')->generate(10);

        $session = $this->newSession();
        $session->into('foo')->generate(2);

        $rows = $this->connection->fetchAll("SELECT * FROM foo;");
        $this->assertCount(2, $rows);
    }

    public function testExistingForeignRecord()
    {
        $session = $this->newSession();

        $session->into('foo_parent')->generate(2);

        $session->into('foo')->generate(3);

        $rows = $this->connection->fetchAll("SELECT * FROM foo;");
        $this->assertEquals(1, $rows[0]['parent_id']);
        $this->assertEquals(2, $rows[1]['parent_id']);
        $this->assertEquals(1, $rows[2]['parent_id']);
    }

    /**
     * @return Database\FixtureSetupSession
     */
    protected function newSession()
    {
        return $this->manager->createSessionManager($this->connection)->newSession();
    }

    protected function setUp()
    {
        $url = 'sqlite:::memory:';
        // $url = 'sqlite:///' . realpath(__DIR__ . '/..') . '/loader-test.sqlite';

        try {
            $this->connection = DriverManager::getConnection(['url' => $url]);
            $this->setUpSchema();
        } catch (DBALException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $this->sqlLogger = new DebugStack();
        // $this->connection->getConfiguration()->setSQLLogger($this->sqlLogger);

        $this->manager = new FixtureManager();

        parent::setUp();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUpSchema()
    {
        $this->connection->exec("PRAGMA foreign_keys=ON;");

        $this->connection->exec("
            DROP TABLE IF EXISTS foo;
            DROP TABLE IF EXISTS foo_parent;
            CREATE TABLE foo_parent (
              id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
              string1 VARCHAR(255) NOT NULL
            );
            CREATE TABLE foo (
              id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
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
              string1 VARCHAR(255) NOT NULL,
              string2 VARCHAR(255) NOT NULL DEFAULT '',
              string3 VARCHAR(255) NULL,
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
