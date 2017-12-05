<?php
namespace Lapaz\QuickBrownFox\Value;

class ForeignKeyReferencedValue implements ValueProviderInterface
{
    /**
     * @var ValueProviderInterface
     */
    protected $foreignValuesProvider;

    /**
     * @var string
     */
    protected $foreignColumn;

    /**
     * @param ValueProviderInterface $foreignValuesProvider
     * @param string $foreignColumn
     */
    public function __construct(ValueProviderInterface $foreignValuesProvider, $foreignColumn)
    {
        $this->foreignValuesProvider = $foreignValuesProvider;
        $this->foreignColumn = $foreignColumn;
    }

    /**
     * @param int $index
     * @return mixed
     */
    public function getAt($index)
    {
        $record = $this->foreignValuesProvider->getAt($index);
        return $record[$this->foreignColumn];
    }
}
