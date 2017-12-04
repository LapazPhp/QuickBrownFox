<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Exception\UnexpectedStateException;
use Lapaz\QuickBrownFox\Fixture\FixtureInterface;
use Lapaz\QuickBrownFox\FixtureManager;
use Lapaz\QuickBrownFox\Fixture\Loader;

class LoaderSession
{
    /**
     * @var FixtureManager
     */
    protected $manager;

    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var string
     */
    protected $reloadedTables;

    /**
     * @var bool
     */
    protected $terminated;

    /**
     * @param RepositoryAggregateInterface $manager
     * @param Loader $loader
     */
    public function __construct(RepositoryAggregateInterface $manager, Loader $loader)
    {
        $this->manager = $manager;
        $this->loader = $loader;
        $this->reloadedTables = [];
        $this->terminated = false;
    }

    /**
     * @param string $table
     * @return TableLoading
     */
    public function intoTable($table)
    {
        return new TableLoading(
            $this,
            $table,
            $this->manager->getFixtureRepositoryFor($table),
            $this->manager->getGeneratorRepositoryFor($table)
        );
    }

    /**
     * @param string $table
     */
    public function emptyTable($table)
    {
        $this->loader->unload($table);
        $this->reloadedTables[$table] = true;
    }

    /**
     * @param string $table
     * @param FixtureInterface $fixtureSource
     * @param int|null $baseIndex
     * @return array
     */
    public function loadFixtureInternal($table, FixtureInterface $fixtureSource, $baseIndex = null)
    {
        if ($this->terminated) {
            throw new UnexpectedStateException("Session already terminated");
        }

        if (!isset($this->reloadedTables[$table])) {
            $this->loader->unload($table);
            $this->reloadedTables[$table] = true;
        }

        return $this->loader->load($table, $fixtureSource, $baseIndex);
    }

    /**
     *
     */
    public function terminate()
    {
        $this->terminated = true;
    }
}
