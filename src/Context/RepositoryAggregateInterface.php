<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

interface RepositoryAggregateInterface
{
    /**
     * @param string $table
     * @return FixtureRepository
     */
    public function getFixtureRepositoryFor($table);

    /**
     * @param string $table
     * @return GeneratorRepository
     */
    public function getGeneratorRepositoryFor($table);
}
