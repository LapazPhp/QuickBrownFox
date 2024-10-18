<?php

namespace Lapaz\QuickBrownFox;

interface TableLoadingInterface
{
    /**
     * Specify the column definitions for the table.
     *
     * The definitions can be below:
     * - string: predefined generator name
     * - [name => value-or-callable, ...]: column name and the value
     *
     * value-or-callable can be:
     * - scalar: fixed value
     * - fn($i) => ...: column name and value generator function
     *
     * @param string|array<string,callable|scalar> $definitions
     * @return static
     */
    public function with(string|array $definitions): static;

    /**
     * Inserts records filled by data produced by stacked Generators.
     *
     * @param int $amount
     * @param int $baseIndex
     * @return list<int|string|false> Generated record's primary key values.
     */
    public function generate(int $amount = 1, int $baseIndex = 0): array;

    /**
     * Inserts records filled by fixed data specified by array.
     * Unspecified column value produced by Generator stack implicitly.
     *
     * @param list<array<string,scalar>>|string $fixture
     * @param int|null $baseIndex
     * @return list<int|string|false> Generated record's primary key values.
     */
    public function load(array|string $fixture, ?int $baseIndex = null): array;
}