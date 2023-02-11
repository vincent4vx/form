<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use Quatrevieux\Form\DataMapper\DataMapperInterface;

/**
 * Type for generate data mapper methods
 *
 * @template M as DataMapperInterface
 */
interface DataMapperTypeGeneratorInterface
{
    /**
     * Generate PHP code of the {@see DataMapperInterface::toDataObject()} method
     *
     * @param M $dataMapper
     *
     * @return string Body of the method
     *
     * @see DataMapperInterface::toDataObject()
     */
    public function generateToDataObject(DataMapperInterface $dataMapper): string;

    /**
     * Generate PHP code of the {@see DataMapperInterface::toArray()} method
     *
     * @param M $dataMapper
     *
     * @return string Body of the method
     *
     * @see DataMapperInterface::toArray()
     */
    public function generateToArray(DataMapperInterface $dataMapper): string;
}
