<?php

namespace Quatrevieux\Form\Instantiator\Generator;

use Nette\PhpGenerator\ClassType;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;

/**
 * @template I as InstantiatorInterface
 */
interface InstantiatorTypeGeneratorInterface
{
    /**
     * @param InstantiatorInterface $instantiator
     * @return bool
     * @psalm-assert-if-true I $instantiator
     */
    public function supports(InstantiatorInterface $instantiator): bool;

    /**
     * @param I $instantiator Instantiator instance to generate
     * @param InstantiatorClass $class Class generator helper
     */
    public function generate(InstantiatorInterface $instantiator, InstantiatorClass $class): void;
}
