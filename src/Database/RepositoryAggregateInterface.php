<?php
namespace Lapaz\QuickBrownFox\Database;

use Faker\Generator as RandomValueGenerator;
use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

interface RepositoryAggregateInterface
{
    /**
     * Returns fixture repository for specified table.
     *
     * @param string $table Table name
     * @return FixtureRepository Table assigned repository of fixture data.
     */
    public function getFixtureRepositoryFor(string $table): FixtureRepository;

    /**
     * Returns generator repository for specified table.
     *
     * @param string $table Table name
     * @return GeneratorRepository Table assigned repository of data generators.
     */
    public function getGeneratorRepositoryFor(string $table): GeneratorRepository;

    /**
     * Returns random value generator implementation, actually Faker's Generator object.
     *
     * @return RandomValueGenerator Random value generator object
     */
    public function getRandomValueGenerator(): RandomValueGenerator;
}
