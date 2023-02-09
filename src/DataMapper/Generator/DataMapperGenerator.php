<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use Quatrevieux\Form\DataMapper\DataMapperInterface;

/**
 * Generate data mapper class
 */
final class DataMapperGenerator
{
    /**
     * @var list<DataMapperTypeGeneratorInterface>
     */
    private array $generators;

    /**
     * @param list<DataMapperTypeGeneratorInterface> $generators
     */
    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
        $this->generators[] = new PublicPropertyDataMapperGenerator();
    }

    /**
     * Generate the data mapper class code
     *
     * @param string $className Class name of the validator class to generate
     * @param DataMapperInterface $dataMapper DataMapper instance to optimise
     *
     * @return string|null The generated code, or null if there is no supported generator found.
     */
    public function generate(string $className, DataMapperInterface $dataMapper): ?string
    {
        foreach ($this->generators as $generator) {
            if (!$generator->supports($dataMapper)) {
                continue;
            }

            $classHelper = new DataMapperClass($className);
            $generator->generate($dataMapper, $classHelper);

            return $classHelper->code();
        }

        return null;
    }
}
