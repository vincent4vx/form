<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use Nette\PhpGenerator\ClassType;
use Quatrevieux\Form\DataMapper\DataMapperInterface;

/**
 * @template I as DataMapperInterface
 */
interface InstantiatorTypeGeneratorInterface
{
    /**
     * @param DataMapperInterface $instantiator
     * @return bool
     * @psalm-assert-if-true I $instantiator
     */
    public function supports(DataMapperInterface $instantiator): bool;

    /**
     * @param I $instantiator Instantiator instance to generate
     * @param DataMapperClass $class Class generator helper
     */
    public function generate(DataMapperInterface $instantiator, DataMapperClass $class): void;
}
