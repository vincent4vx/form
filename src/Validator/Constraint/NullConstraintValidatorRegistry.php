<?php

namespace Quatrevieux\Form\Validator\Constraint;

use BadMethodCallException;

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
}
