<?php

namespace Quatrevieux\Form;

use Psr\Container\ContainerInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;

/**
 * Implementation of registries using PSR-11 container
 */
final class ContainerRegistry implements FieldTransformerRegistryInterface, ConstraintValidatorRegistryInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator(string $className): ConstraintValidatorInterface
    {
        return $this->container->get($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformer(string $className): ConfigurableFieldTransformerInterface
    {
        return $this->container->get($className);
    }
}
