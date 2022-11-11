<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Instantiator\InstantiatorFactoryInterface;
use Quatrevieux\Form\Instantiator\RuntimeInstantiatorFactory;
use Quatrevieux\Form\Validator\Constraint\ContainerConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;

/**
 * Default implementation of FormFactoryInterface
 */
final class DefaultFormFactory implements FormFactoryInterface
{
    public function __construct(
        private readonly InstantiatorFactoryInterface $instantiatorFactory = new RuntimeInstantiatorFactory(),
        private readonly ValidatorFactoryInterface $validatorFactory = new RuntimeValidatorFactory(new ContainerConstraintValidatorRegistry()),
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): FormInterface
    {
        return new Form(
            $this->instantiatorFactory->create($dataClass),
            $this->validatorFactory->create($dataClass),
        );
    }
}
