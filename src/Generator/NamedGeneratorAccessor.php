<?php
namespace Lapaz\QuickBrownFox\Generator;

use Lapaz\QuickBrownFox\Value\ValueProviderInterface;

class NamedGeneratorAccessor implements GeneratorInterface
{
    /**
     * @param GeneratorRepository $repository
     * @param string $name
     */
    public function __construct(
        protected GeneratorRepository $repository,
        protected string $name
    )
    {
    }

    /**
     * @param int $index
     * @return list<ValueProviderInterface>
     */
    public function extractAt(int $index): array
    {
        return $this->repository->get($this->name)->extractAt($index);
    }

    /**
     * @param int $index
     * @return array<string, mixed>
     */
    public function generateAt(int $index): array
    {
        return $this->repository->get($this->name)->generateAt($index);
    }
}
