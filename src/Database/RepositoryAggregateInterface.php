<?php
namespace Lapaz\QuickBrownFox\Database;

use Faker\Generator as RandomValueGenerator;
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

    /**
     * @return RandomValueGenerator
     */
    public function getRandomValueGenerator();
}
