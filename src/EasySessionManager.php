<?php

namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Lapaz\QuickBrownFox\Database\FixtureSetupSession;
use Lapaz\QuickBrownFox\Database\Loader;
use Lapaz\QuickBrownFox\Database\NullRepositoryAggregate;
use Lapaz\QuickBrownFox\Database\TablePrototypeGeneratorBuilder;

/**
 * Simple implementation of SessionManagerInterface.
 *
 * This class can be used like a factory to create new FixtureSetupSession.
 * But it does not support by-name predefined defaults, generators nor fixtures.
 *
 * This class aims to simplify DI configuration for beginners.
 */
class EasySessionManager implements SessionManagerInterface
{
    /**
     * @var FixtureSetupSession|null
     */
    protected ?FixtureSetupSession $currentSession;

    private Loader $loader;

    private NullRepositoryAggregate $repositoryAggregate;

    private TablePrototypeGeneratorBuilder $tablePrototypeGeneratorBuilder;

    /**
     * @param Connection $connection
     */
    public function __construct(
        protected Connection $connection,
    ) {
        $this->currentSession = null;
        $this->loader = new Loader($this->connection);
        $this->repositoryAggregate = new NullRepositoryAggregate();
        $this->tablePrototypeGeneratorBuilder = new TablePrototypeGeneratorBuilder($this->connection, $this->repositoryAggregate);
    }

    /**
     * @inheritDoc
     */
    public function newSession(): FixtureSetupSessionInterface
    {
        $this->currentSession?->terminate();

        $this->currentSession = new FixtureSetupSession(
            $this->repositoryAggregate,
            $this->loader,
            $this->tablePrototypeGeneratorBuilder
        );
        return $this->currentSession;
    }
}