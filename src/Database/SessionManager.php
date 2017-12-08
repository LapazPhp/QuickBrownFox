<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;

class SessionManager
{
    /**
     * @var RepositoryAggregateInterface
     */
    protected $repositoryAggregate;

    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var TablePrototypeGeneratorBuilder
     */
    private $prototypeBuilder;

    /**
     * @var FixtureSetupSession
     */
    protected $currentSession;

    /**
     * @param RepositoryAggregateInterface $repositoryAggregate
     * @param Connection $connection
     */
    public function __construct(
        RepositoryAggregateInterface $repositoryAggregate,
        Connection $connection
    )
    {
        $this->repositoryAggregate = $repositoryAggregate;

        $this->prototypeBuilder = new TablePrototypeGeneratorBuilder(
            $connection,
            $repositoryAggregate->getRandomValueGenerator()
        );

        $this->loader = new Loader($connection);

        $this->currentSession = null;
    }

    /**
     * @return FixtureSetupSession
     */
    public function newSession()
    {
        if ($this->currentSession) {
            $this->currentSession->terminate();
        }
        $this->currentSession = new FixtureSetupSession(
            $this->repositoryAggregate,
            $this->loader,
            $this->prototypeBuilder
        );
        return $this->currentSession;
    }
}
