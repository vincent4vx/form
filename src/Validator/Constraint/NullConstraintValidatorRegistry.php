<?php

namespace Quatrevieux\Form\Validator\Constraint;

use BadMethodCallException;
use Quatrevieux\Form\DummyTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Null-object for validator registry
 */
final class NullConstraintValidatorRegistry implements ConstraintValidatorRegistryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValidator(string $className): ConstraintValidatorInterface
    {
        throw new BadMethodCallException('Cannot use external validator : no container or custom registry defined.');
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslator(): TranslatorInterface
    {
        return DummyTranslator::instance();
    }
}
