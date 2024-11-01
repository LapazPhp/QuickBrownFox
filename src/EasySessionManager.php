<?php

namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use Lapaz\QuickBrownFox\Database\FixtureSetupSession;
use Lapaz\QuickBrownFox\Database\Loader;
use Lapaz\QuickBrownFox\Database\NullRepositoryAggregate;
use Lapaz\QuickBrownFox\Database\TablePrototypeGeneratorBuilder;
use Lapaz\QuickBrownFox\Exception\DatabaseException;

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

    /**
     * @var Connection
     */
    protected Connection $connection;

    private Loader $loader;

    private NullRepositoryAggregate $repositoryAggregate;

    private TablePrototypeGeneratorBuilder $tablePrototypeGeneratorBuilder;

    /**
     * @param Connection $connection
     */
    public function __construct(
        Connection|array $connection,
    ) {
        // PDO connection is no longer supported.
        // https://github.com/doctrine/dbal/blob/3.0.0/UPGRADE.md#bc-break-user-provided-pdo-instance-is-no-longer-supported
        if (is_array($connection)) {
            try {
                $connection = DriverManager::getConnection($connection);
            } catch (DBALException $e) {
                throw DatabaseException::fromDBALException($e);
            }
        }
        $this->connection = $connection;
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