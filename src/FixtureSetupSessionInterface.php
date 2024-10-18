<?php

namespace Lapaz\QuickBrownFox;

interface FixtureSetupSessionInterface
{
    /**
     * Specify the table to load fixture data.
     *
     * @param string $table
     * @return TableLoadingInterface
     */
    public function into(string $table): TableLoadingInterface;

    /**
     * Reset the table to initial state.
     * (except for the auto-increment value)
     *
     * @param string $table
     */
    public function reset(string $table): void;

    /**
     * Terminate the session explicitly.
     *
     * @return void
     */
    public function terminate(): void;
}