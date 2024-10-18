<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Generator\GeneratorInterface;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

trait WithContextTrait
{
    /**
     * @var GeneratorRepository
     */
    protected GeneratorRepository $generatorRepository;

    /**
     * @var GeneratorInterface[]
     */
    protected array $generators;

    /**
     * @param string|array<string,callable|scalar> $definitions
     * @return static
     */
    public function with(string|array $definitions): static
    {
        // Normalize definitions to array even if single definition.
        $d = $definitions;
        if (
            !is_array($d) ||
            !empty($d) && array_keys($d) !== range(0, count($d) - 1)
        ) {
            $definitions = [$d];
        }

        $context = clone $this;
        foreach ($definitions as $definition) {
            $context->generators[] = $this->generatorRepository->normalizeGenerator($definition);
        }

        return $context;
    }

}
