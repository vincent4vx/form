<?php

namespace Quatrevieux\Form\Instantiator\Generator;

use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;

final class InstantiatorGenerator
{
    /**
     * @var list<InstantiatorTypeGeneratorInterface>
     */
    private array $generators;

    /**
     * @param list<InstantiatorTypeGeneratorInterface> $generators
     */
    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
        $this->generators[] = new PublicPropertyInstantiatorGenerator();
    }

    /**
     * Generate the instantiator class code
     *
     * @param InstantiatorInterface $instantiator Instantiator instance to optimise
     * @param GeneratedInstantiatorFactory $factory Instantiator factory, used to resolve instantiator class name
     *
     * @return string|null The generated code, or null if there is no supported generator found.
     */
    public function generate(InstantiatorInterface $instantiator, GeneratedInstantiatorFactory $factory): ?string
    {
        foreach ($this->generators as $generator) {
            if (!$generator->supports($instantiator)) {
                continue;
            }

            $classHelper = new InstantiatorClass($factory->resolveClassName($instantiator->className()));
            $generator->generate($instantiator, $classHelper);

            return $classHelper->code();
        }

        return null;
    }
}
