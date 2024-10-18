<?php

namespace Lapaz\QuickBrownFox;

interface TableDefinitionInterface
{

    /**
     * Starts the table default definition.
     *
     * @return TableDefinitionGeneratorInterface
     */
    public function defaults(): TableDefinitionGeneratorInterface;

    /**
     * Starts a predefined table generator definition.
     *
     * @param string $name
     * @return TableDefinitionGeneratorInterface
     */
    public function generator(string $name): TableDefinitionGeneratorInterface;

    /**
     * Starts a predefined table fixture definition.
     *
     * @param string $name
     * @return TableDefinitionFixtureInterface
     */
    public function fixture(string $name): TableDefinitionFixtureInterface;
}