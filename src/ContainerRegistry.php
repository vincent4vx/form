<?php

namespace Quatrevieux\Form;

use Psr\Container\ContainerInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        // @phpstan-ignore-next-line
        return $this->container->get($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslator(): TranslatorInterface
    {
        // @phpstan-ignore-next-line
        return $this->container->has(TranslatorInterface::class)
            ? $this->container->get(TranslatorInterface::class)
            : DummyTranslator::instance()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformer(string $className): ConfigurableFieldTransformerInterface
    {
        // @phpstan-ignore-next-line
        return $this->container->get($className);
    }
}
