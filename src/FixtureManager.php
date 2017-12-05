<?php
namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Faker\Factory as RandomValueFactory;
use Faker\Generator as RandomValueGenerator;
use Lapaz\QuickBrownFox\Context\TableDefinition;
use Lapaz\QuickBrownFox\Database\TablePrototypeGeneratorBuilder;
use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class FixtureManager
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
     * @var FixtureSetupSession
     */
    protected $currentSession;

    /**
     * @param string $locale
     */
    public function __construct($locale = RandomValueFactory::DEFAULT_LOCALE)
    {
        $this->randomValueGenerator = RandomValueFactory::create($locale);

        $this->fixtureRepositories = [];
        $this->generatorRepositories = [];
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
     * @param Connection $connection
     * @return FixtureSetupSession
     */
    public function newSession(Connection $connection)
    {
        if ($this->currentSession) {
            $this->currentSession->terminate();
        }

        $builder = new TablePrototypeGeneratorBuilder(
            $connection,
            $this->randomValueGenerator
        );

        $this->currentSession = new FixtureSetupSession($connection, $this, $builder);

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
