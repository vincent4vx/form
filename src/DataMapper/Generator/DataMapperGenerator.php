<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use Quatrevieux\Form\DataMapper\DataMapperInterface;

/**
 * Generate data mapper class
 */
final class DataMapperGenerator
{
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
        if (!$dataMapper instanceof DataMapperTypeGeneratorInterface) {
            return null;
        }

        $classHelper = new DataMapperClass($className);

        $classHelper->setClassName($dataMapper->className());
        $classHelper->setToDataObjectBody($dataMapper->generateToDataObject($dataMapper));
        $classHelper->setToArrayBody($dataMapper->generateToArray($dataMapper));

        return $classHelper->code();
    }
}
