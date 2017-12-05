<?php
namespace Lapaz\QuickBrownFox;

use Doctrine\DBAL\Connection;
use Lapaz\QuickBrownFox\Context\FixtureLoadableInterface;
use Lapaz\QuickBrownFox\Context\TableLoading;
use Lapaz\QuickBrownFox\Database\Loader;
use Lapaz\QuickBrownFox\Database\TablePrototypeGeneratorBuilder;
use Lapaz\QuickBrownFox\Exception\UnexpectedStateException;
use Lapaz\QuickBrownFox\Fixture\FixtureInterface;

class FixtureSetupSession implements FixtureLoadableInterface
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
     * @param Connection $connection
     * @param FixtureManager $manager
     * @param TablePrototypeGeneratorBuilder $prototypeBuilder
     */
    public function __construct(
        Connection $connection,
        FixtureManager $manager,
        TablePrototypeGeneratorBuilder $prototypeBuilder
    )
    {
        $this->manager = $manager;
        $this->loader = new Loader($connection);
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
            $this->manager->getFixtureRepositoryFor($table),
            $this->manager->getGeneratorRepositoryFor($table)
        );
    }

    /**
     * @param string $table
     */
    public function truncate($table)
    {
        $this->loader->truncate($table);
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
            $this->loader->truncate($table);
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
