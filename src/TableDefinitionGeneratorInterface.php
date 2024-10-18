<?php

namespace Lapaz\QuickBrownFox;

interface TableDefinitionGeneratorInterface
{
    /**
     *  @param callable|array<string,callable|scalar> $definition
     */
    public function define(callable|array $definition = []): void;
}
