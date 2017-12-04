<?php
namespace Lapaz\QuickBrownFox\Value;

interface ValueProviderInterface
{
    /**
     * @param int $index
     * @return mixed
     */
    public function getAt($index);
}
