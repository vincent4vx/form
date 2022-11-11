<?php

namespace Quatrevieux\Form\Validator\Constraint;

/**
 * Base class for simple constraint which can be validated by it-self instead of an external validator instance
 *
 * @implements ConstraintValidatorInterface<static>
 */
abstract class SelfValidatedConstraint implements ConstraintInterface, ConstraintValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public final function getValidator(ConstraintValidatorRegistryInterface $registry): ConstraintValidatorInterface
    {
        return $this;
    }
}
