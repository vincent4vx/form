<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use Quatrevieux\Form\DataMapper\DataMapperInterface;

/**
 * Type for generate data mapper methods
 *
 * @template I as DataMapperInterface
 */
interface DataMapperTypeGeneratorInterface
{
    /**
     * @param DataMapperInterface $dataMapper
     * @return bool
     * @psalm-assert-if-true I $dataMapper
     */
    public function supports(DataMapperInterface $dataMapper): bool;

    /**
     * @param I $dataMapper Data mapper instance to generate
     * @param DataMapperClass $class Class generator helper
     */
    public function generate(DataMapperInterface $dataMapper, DataMapperClass $class): void;
}
