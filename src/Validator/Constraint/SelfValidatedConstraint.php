<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\RegistryInterface;

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
    final public function getValidator(RegistryInterface $registry): ConstraintValidatorInterface
    {
        return $this;
    }
}
