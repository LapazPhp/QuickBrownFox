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
     * @param string $locale
     */
    public function __construct($locale = RandomValueFactory::DEFAULT_LOCALE)
    {
        $this->randomValueGenerator = RandomValueFactory::create($locale);

        $this->fixtureRepositories = [];
        $this->generatorRepositories = [];
    }

    /**
     * @param string $table
     * @return FixtureRepository
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
     * @param string $table
     * @return GeneratorRepository
     */
    public function getGeneratorRepositoryFor($table)
    {
        if (!isset($this->generatorRepositories[$table])) {
            $this->generatorRepositories[$table] = new GeneratorRepository();
        }
        return $this->generatorRepositories[$table];
    }

    /**
     * @return RandomValueGenerator
     */
    public function getRandomValueGenerator()
    {
        return $this->randomValueGenerator;
    }

    /**
     * @param string $table
     * @param callable $callable
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
     * @param Connection|\PDO $connection
     * @return SessionManager
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
