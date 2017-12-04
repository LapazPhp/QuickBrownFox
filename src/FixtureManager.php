<?php
namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Faker\Factory;
use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;
use Lapaz\QuickBrownFox\Fixture\Loader;
use Lapaz\QuickBrownFox\Context\TableDefinition;
use Lapaz\QuickBrownFox\Context\LoaderSession;
use Lapaz\QuickBrownFox\Context\RepositoryAggregateInterface;
use Lapaz\QuickBrownFox\Generator\TablePrototypeGeneratorBuilder;

/**
 * Class FixtureManager
 */
class FixtureManager implements RepositoryAggregateInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var FixtureRepository[]
     */
    protected $fixtureRepositories;

    /**
     * @var GeneratorRepository[]
     */
    protected $generatorRepositories;

    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var LoaderSession
     */
    protected $currentSession;

    /**
     * @param Connection $connection
     * @param string $locale
     */
    public function __construct(Connection $connection, $locale = Factory::DEFAULT_LOCALE)
    {
        $this->connection = $connection;
        $this->fixtureRepositories = [];
        $this->generatorRepositories = [];
        $prototypeBuilder = new TablePrototypeGeneratorBuilder($this->connection, Factory::create($locale));
        $this->loader = new Loader($this->connection, $prototypeBuilder);
        $this->currentSession = null;
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
     * @return LoaderSession
     */
    public function newSession()
    {
        if ($this->currentSession) {
            $this->currentSession->terminate();
        }
        $this->currentSession = new LoaderSession($this, $this->loader);
        return $this->currentSession;
    }

//    /**
//     * @return LoaderSession
//     */
//    public function getCurrentSession()
//    {
//        if (!$this->currentSession) {
//            $this->newSession();
//        }
//
//        return $this->currentSession;
//    }
}
