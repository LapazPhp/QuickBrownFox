<?php

namespace Lapaz\QuickBrownFox\Database;

use Faker\Factory as RandomValueFactory;
use Faker\Generator as RandomValueGenerator;
use Lapaz\QuickBrownFox\Fixture\FixtureRepository;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

class NullRepositoryAggregate implements RepositoryAggregateInterface
{
    private static ?self $instance = null;

    private FixtureRepository $fixtureRepository;
    private GeneratorRepository $generatorRepository;
    private RandomValueGenerator $randomValueGenerator;

    public function __construct()
    {
        $this->fixtureRepository = new FixtureRepository(new GeneratorRepository());
        $this->generatorRepository = new GeneratorRepository();
        $this->randomValueGenerator = RandomValueFactory::create();
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function getFixtureRepositoryFor(string $table): FixtureRepository
    {
        return $this->fixtureRepository;
    }

    public function getGeneratorRepositoryFor(string $table): GeneratorRepository
    {
        return $this->generatorRepository;
    }

    public function getRandomValueGenerator(): RandomValueGenerator
    {
        return $this->randomValueGenerator;
    }
}