<?php

namespace Lapaz\QuickBrownFox;

interface SessionManagerInterface
{
    /**
     * Create new session for fixture setup.
     *
     * @return FixtureSetupSessionInterface
     */
    public function newSession(): FixtureSetupSessionInterface;
}
