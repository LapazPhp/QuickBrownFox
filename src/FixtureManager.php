<?php
namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
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
     * @var array<string,FixtureRepository>
     */
    protected array $fixtureRepositories;

    /**
     * @var array<string,GeneratorRepository>
     */
    protected array $generatorRepositories;

    /**
     * @var RandomValueGenerator
     */
    protected RandomValueGenerator $randomValueGenerator;

    /**
     * Creates initialized FixtureManager.
     *
     * @param string $locale Locale of randomly generated text e.g. 'ja_JP'
     */
    public function __construct(string $locale = RandomValueFactory::DEFAULT_LOCALE)
    {
        $this->randomValueGenerator = RandomValueFactory::create($locale);

        $this->fixtureRepositories = [];
        $this->generatorRepositories = [];
    }

    /**
     * @inheritdoc
     */
    public function getFixtureRepositoryFor(string $table): FixtureRepository
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
    public function getGeneratorRepositoryFor(string $table): GeneratorRepository
    {
        if (!isset($this->generatorRepositories[$table])) {
            $this->generatorRepositories[$table] = new GeneratorRepository();
        }
        return $this->generatorRepositories[$table];
    }

    /**
     * @inheritdoc
     */
    public function getRandomValueGenerator(): RandomValueGenerator
    {
        return $this->randomValueGenerator;
    }

    /**
     * Defines table fixtures and generators by callable which takes
     * TableDefinition instance.
     *
     * @param string $table Table name
     * @param callable(TableDefinition):void $callable Definition procedure callback
     */
    public function table(string $table, callable $callable): void
    {
        $context = new TableDefinition(
            $this->getFixtureRepositoryFor($table),
            $this->getGeneratorRepositoryFor($table)
        );
        $callable($context);
    }

    /**
     * Creates a session manager from DBAL database connection.
     *
     * @param Connection|array $connection DBAL connection
     * @return SessionManager Session manager
     */
    public function createSessionManager(Connection|array $connection): SessionManager
    {
        // PDO connection is no longer supported.
        // https://github.com/doctrine/dbal/blob/3.0.0/UPGRADE.md#bc-break-user-provided-pdo-instance-is-no-longer-supported
        if (is_array($connection)) {
            try {
                $connection = DriverManager::getConnection($connection);
            } catch (DBALException $e) {
                throw DatabaseException::fromDBALException($e);
            }
        }

        return new SessionManager($this, $connection);
    }
}
