<?php

namespace Quatrevieux\Form;

use Psr\Container\ContainerInterface;
use Quatrevieux\Form\DataMapper\DataMapperFactoryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Implementation of registry using PSR-11 container
 */
final class ContainerRegistry implements RegistryInterface
{
    use RegistryTrait;

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintValidator(string $className): ConstraintValidatorInterface
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
    public function getFieldTransformer(string $className): ConfigurableFieldTransformerInterface
    {
        // @phpstan-ignore-next-line
        return $this->container->get($className);
    }
}
