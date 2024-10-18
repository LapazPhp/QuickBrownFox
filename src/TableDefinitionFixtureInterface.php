<?php

namespace Lapaz\QuickBrownFox;

interface TableDefinitionFixtureInterface
{

    /**
     * @param list<array<string,scalar>> $records
     */
    public function define(array $records): void;

    /**
     * @param callable|array<string,callable|scalar>|string $generator
     * @param int $repeatAmount
     * @param int $baseIndex
     */
    public function defineGenerated(
        callable|array|string $generator,
        int $repeatAmount,
        int $baseIndex = 0
    ): void;
}
