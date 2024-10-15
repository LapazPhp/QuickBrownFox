<?php
namespace Lapaz\QuickBrownFox\Database;

use Lapaz\QuickBrownFox\Context\FixtureLoadableInterface;
use Lapaz\QuickBrownFox\Context\TableLoading;
use Lapaz\QuickBrownFox\Exception\UnexpectedStateException;
use Lapaz\QuickBrownFox\Fixture\FixtureInterface;

class FixtureSetupSession implements FixtureLoadableInterface
{
    /**
     * @var array<string, bool>
     */
    protected array $reloadedTables;

    /**
     * @var bool
     */
    protected bool $terminated;

    /**
     * @param RepositoryAggregateInterface $repositoryAggregate
     * @param Loader $loader
     * @param TablePrototypeGeneratorBuilder $prototypeBuilder
     */
    public function __construct(
        protected RepositoryAggregateInterface $repositoryAggregate,
        protected Loader $loader,
        protected TablePrototypeGeneratorBuilder $prototypeBuilder
    )
    {
        $this->reloadedTables = [];
        $this->terminated = false;
    }

    /**
     * @param string $table
     * @return TableLoading
     */
    public function into(string $table): TableLoading
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
    public function reset(string $table): void
    {
        $this->loader->resetCascading($table);
        $this->reloadedTables[$table] = true;
    }

    /**
     * @param string $table
     * @param FixtureInterface $fixtureSource
     * @param int|null $baseIndex
     * @return list<int|string>
     */
    public function load(string $table, FixtureInterface $fixtureSource, ?int $baseIndex = null): array
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
    public function terminate(): void
    {
        $this->terminated = true;
    }
}
