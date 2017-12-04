<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class NamedGeneratorAccessor implements GeneratorInterface
{
    /**
     * @var GeneratorRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param GeneratorRepository $repository
     * @param string $name
     */
    public function __construct(GeneratorRepository $repository, $name)
    {
        $this->repository = $repository;
        $this->name = $name;
    }

    /**
     * @param int $index
     * @return ValueProviderInterface[]
     */
    public function extractAt($index)
    {
        return $this->repository->get($this->name)->extractAt($index);
    }

    /**
     * @param int $index
     * @return array
     */
    public function generateAt($index)
    {
        return $this->repository->get($this->name)->generateAt($index);
    }
}
