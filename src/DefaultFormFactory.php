<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Instantiator\InstantiatorFactoryInterface;
use Quatrevieux\Form\Instantiator\RuntimeInstantiatorFactory;
use Quatrevieux\Form\Transformer\Field\NullFieldTransformerRegistry;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Transformer\RuntimeFormTransformerFactory;
use Quatrevieux\Form\Validator\Constraint\NullConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;

/**
 * Default implementation of FormFactoryInterface
 *
 * @todo factory method with container and code generator config
 */
final class DefaultFormFactory implements FormFactoryInterface
{
    public function __construct(
        private readonly InstantiatorFactoryInterface $instantiatorFactory = new RuntimeInstantiatorFactory(),
        private readonly ValidatorFactoryInterface $validatorFactory = new RuntimeValidatorFactory(new NullConstraintValidatorRegistry()),
        private readonly FormTransformerFactoryInterface $transformerFactory = new RuntimeFormTransformerFactory(new NullFieldTransformerRegistry()),
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClass): FormInterface
    {
        return new Form(
            $this->transformerFactory->create($dataClass),
            $this->instantiatorFactory->create($dataClass),
            $this->validatorFactory->create($dataClass),
        );
    }
}
