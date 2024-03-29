<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\DataMapper\DataMapperFactoryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;
use Quatrevieux\Form\View\FormViewInstantiatorFactoryInterface;
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
    public function getFieldTransformer(string $className): ConfigurableFieldTransformerInterface;

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
    public function getConstraintValidator(string $className): ConstraintValidatorInterface;

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
    public function getDataMapperFactory(): DataMapperFactoryInterface;

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
    public function getFormViewInstantiatorFactory(): FormViewInstantiatorFactoryInterface;

    /**
     * @internal
     */
    public function setDataMapperFactory(DataMapperFactoryInterface $factory): void;

    /**
     * @internal
     */
    public function setTransformerFactory(FormTransformerFactoryInterface $factory): void;

    /**
     * @internal
     */
    public function setValidatorFactory(ValidatorFactoryInterface $factory): void;

    /**
     * @internal
     */
    public function setFormViewInstantiatorFactory(FormViewInstantiatorFactoryInterface $factory): void;
}
