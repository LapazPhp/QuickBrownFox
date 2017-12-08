<?php
namespace Lapaz\QuickBrownFox\Database;

use Lapaz\QuickBrownFox\Context\FixtureLoadableInterface;
use Lapaz\QuickBrownFox\Context\TableLoading;
use Lapaz\QuickBrownFox\Exception\UnexpectedStateException;
use Lapaz\QuickBrownFox\Fixture\FixtureInterface;

class FixtureSetupSession implements FixtureLoadableInterface
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
     * @var string
     */
    protected $reloadedTables;

    /**
     * @var bool
     */
    protected $terminated;

    /**
     * @param RepositoryAggregateInterface $repositoryAggregate
     * @param Loader $loader
     * @param TablePrototypeGeneratorBuilder $prototypeBuilder
     */
    public function __construct(
        RepositoryAggregateInterface $repositoryAggregate,
        Loader $loader,
        TablePrototypeGeneratorBuilder $prototypeBuilder
    )
    {
        $this->repositoryAggregate = $repositoryAggregate;
        $this->loader = $loader;
        $this->prototypeBuilder = $prototypeBuilder;

        $this->reloadedTables = [];
        $this->terminated = false;
    }

    /**
     * @param string $table
     * @return TableLoading
     */
    public function into($table)
    {
        return new TableLoading(
            $this,
            $table,
            $this->repositoryAggregate->getFixtureRepositoryFor($table),
            $this->repositoryAggregate->getGeneratorRepositoryFor($table)
        );
    }

    /**
     * @param string $table
     */
    public function reset($table)
    {
        $this->loader->resetCascading($table);
        $this->reloadedTables[$table] = true;
    }

    /**
     * @param string $table
     * @param FixtureInterface $fixtureSource
     * @param int|null $baseIndex
     * @return array
     */
    public function load($table, FixtureInterface $fixtureSource, $baseIndex = null)
    {
        if ($this->terminated) {
            throw new UnexpectedStateException("Session already terminated");
        }

        if (!isset($this->reloadedTables[$table])) {
            $this->loader->resetCascading($table);
            $this->reloadedTables[$table] = true;
        }

        $records = $fixtureSource->generateRecords(
            $this->prototypeBuilder->build($table),
            $baseIndex
        );

        return $this->loader->load($table, $records);
    }

    /**
     *
     */
    public function terminate()
    {
        $this->terminated = true;
    }
}
