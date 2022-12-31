<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Instantiator\InstantiatorFactoryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Registry for internal components
 * This registry should perform dependency injection if needed
 */
interface RegistryInterface
{
    /**
     * Get transformer implementation for a {@see DelegatedFieldTransformerInterface}
     *
     * @param class-string<T> $className Class name of the implementation
     *
     * @return T
     * @template T as ConfigurableFieldTransformerInterface
     */
    public function getTransformer(string $className): ConfigurableFieldTransformerInterface;

    /**
     * Get a validator instance
     *
     * @param class-string<V> $className Validator class name
     *
     * @return V
     * @template V as ConstraintValidatorInterface
     *
     * @see ConstraintInterface::getValidator()
     */
    public function getValidator(string $className): ConstraintValidatorInterface;

    /**
     * Get the configured translator instance
     * If no translator is configured, a {@see DummyTranslator} will be returned
     *
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface;

    /**
     * @internal
     */
    public function getInstantiatorFactory(): InstantiatorFactoryInterface;

    /**
     * @internal
     */
    public function getTransformerFactory(): FormTransformerFactoryInterface;

    /**
     * @internal
     */
    public function getValidatorFactory(): ValidatorFactoryInterface;

    /**
     * @internal
     */
    public function setInstantiatorFactory(InstantiatorFactoryInterface $factory): void;

    /**
     * @internal
     */
    public function setTransformerFactory(FormTransformerFactoryInterface $factory): void;

    /**
     * @internal
     */
    public function setValidatorFactory(ValidatorFactoryInterface $factory): void;
}
