<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use Quatrevieux\Form\DataMapper\DataMapperInterface;

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
     * @param string $className Class name of the validator class to generate
     * @param DataMapperInterface $instantiator Instantiator instance to optimise
     *
     * @return string|null The generated code, or null if there is no supported generator found.
     */
    public function generate(string $className, DataMapperInterface $instantiator): ?string
    {
        foreach ($this->generators as $generator) {
            if (!$generator->supports($instantiator)) {
                continue;
            }

            $classHelper = new DataMapperClass($className);
            $generator->generate($instantiator, $classHelper);

            return $classHelper->code();
        }

        return null;
    }
}
