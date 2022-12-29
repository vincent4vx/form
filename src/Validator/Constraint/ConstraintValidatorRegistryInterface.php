<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\DummyTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Registry of validator instances
 */
interface ConstraintValidatorRegistryInterface
{
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
}
