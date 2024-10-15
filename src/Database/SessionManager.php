<?php
namespace Lapaz\QuickBrownFox\Database;

use Doctrine\DBAL\Connection;

class SessionManager
{
    /**
     * @var Loader
     */
    protected Loader $loader;

    /**
     * @var TablePrototypeGeneratorBuilder
     */
    private TablePrototypeGeneratorBuilder $prototypeBuilder;

    /**
     * @var FixtureSetupSession|null
     */
    protected ?FixtureSetupSession $currentSession;

    /**
     * @param RepositoryAggregateInterface $repositoryAggregate
     * @param Connection $connection
     */
    public function __construct(
        protected RepositoryAggregateInterface $repositoryAggregate,
        Connection $connection
    )
    {
        $this->prototypeBuilder = new TablePrototypeGeneratorBuilder(
            $connection,
            $repositoryAggregate
        );

        $this->loader = new Loader($connection);

        $this->currentSession = null;
    }

    /**
     * @return FixtureSetupSession
     */
    public function newSession(): FixtureSetupSession
    {
        $this->currentSession?->terminate();

        $this->currentSession = new FixtureSetupSession(
            $this->repositoryAggregate,
            $this->loader,
            $this->prototypeBuilder
        );
        return $this->currentSession;
    }
}
