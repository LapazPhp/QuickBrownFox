<?php
namespace Lapaz\QuickBrownFox\Value;

class ForeignKeyReferencedValue implements ValueProviderInterface
{
    /**
     * @param ValueProviderInterface $foreignValuesProvider
     * @param string $foreignColumn
     */
    public function __construct(
        protected ValueProviderInterface $foreignValuesProvider,
        protected string $foreignColumn
    )
    {
    }

    /**
     * @param int $index
     * @return mixed
     */
    public function getAt(int $index): mixed
    {
        $record = $this->foreignValuesProvider->getAt($index);
        return $record[$this->foreignColumn];
    }
}
