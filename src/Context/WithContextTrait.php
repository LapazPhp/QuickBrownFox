<?php
namespace Lapaz\QuickBrownFox\Context;

use Lapaz\QuickBrownFox\Generator\GeneratorInterface;
use Lapaz\QuickBrownFox\Generator\GeneratorRepository;

trait WithContextTrait
{
    /**
     * @var GeneratorRepository
     */
    protected $generatorRepository;

    /**
     * @var GeneratorInterface[]
     */
    protected $generators;

    /**
     * @param mixed[]|mixed $definitions
     * @return static
     */
    public function with($definitions)
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
