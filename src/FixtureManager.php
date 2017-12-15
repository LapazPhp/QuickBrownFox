<?php
namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Faker\Factory as RandomValueFactory;
use Faker\Generator as RandomValueGenerator;
use Lapaz\QuickBrownFox\Context\TableDefinition;
use Lapaz\QuickBrownFox\Database\RepositoryAggregateInterface;
use Lapaz\QuickBrownFox\Database\SessionManager;
use Lapaz\QuickBrownFox\Exception\DatabaseException;
use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

/**
 * FixtureManager is the root object of fixture management workflow. This
 * object contains predefined generator/fixture repositories for database
 * tables.
 *
 * It can create a session manager. The fixture setup session which corresponds
 * a test case must be isolated from other test cases. SessionManager object
 * creates a new session for your test scenario. You can load several fixtures
 * within a single session at once.
 */
class FixtureManager implements RepositoryAggregateInterface
{
    /**
     * @var FixtureRepository[]
     */
    protected $fixtureRepositories;

    /**
     * @var GeneratorRepository[]
     */
    protected $generatorRepositories;

    /**
     * @var RandomValueGenerator
     */
    protected $randomValueGenerator;

    /**
     * Creates initialized FixtureManager.
     *
     * @param string $locale Locale of randomly generated text e.g. 'ja_JP'
     */
    public function __construct($locale = RandomValueFactory::DEFAULT_LOCALE)
    {
        $this->randomValueGenerator = RandomValueFactory::create($locale);

        $this->fixtureRepositories = [];
        $this->generatorRepositories = [];
    }

    /**
     * @inheritdoc
     */
    public function getFixtureRepositoryFor($table)
    {
        if (!isset($this->fixtureRepositories[$table])) {
            $this->fixtureRepositories[$table] = new FixtureRepository(
                $this->getGeneratorRepositoryFor($table)
            );
        }
        return $this->fixtureRepositories[$table];
    }

    /**
     * @inheritdoc
     */
    public function getGeneratorRepositoryFor($table)
    {
        if (!isset($this->generatorRepositories[$table])) {
            $this->generatorRepositories[$table] = new GeneratorRepository();
        }
        return $this->generatorRepositories[$table];
    }

    /**
     * @inheritdoc
     */
    public function getRandomValueGenerator()
    {
        return $this->randomValueGenerator;
    }

    /**
     * Defines table fixtures and generators by callable which takes
     * TableDefinition instance.
     *
     * @param string $table Table name
     * @param callable $callable Definition procedure callback
     */
    public function table($table, $callable)
    {
        $context = new TableDefinition(
            $this->getFixtureRepositoryFor($table),
            $this->getGeneratorRepositoryFor($table)
        );
        $callable($context);
    }

    /**
     * Creates a session manager from database connection.
     * It allows both of PDO and Doctrine DBAL.
     *
     * @param Connection|\PDO $connection Database connection
     * @return SessionManager Session manager
     */
    public function createSessionManager($connection)
    {
        if (!($connection instanceof Connection)) {
            try {
                $connection = DriverManager::getConnection(['pdo' => $connection]);
            } catch (DBALException $e) {
                throw DatabaseException::fromDBALException($e);
            }
        }

        return new SessionManager($this, $connection);
    }
}
