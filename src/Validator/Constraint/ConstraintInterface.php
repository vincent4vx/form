<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Validator\FieldError;

/**
 * Base type for define a constraint
 * The implementation must be declared as attribute using `#[Attribute(Attribute::TARGET_PROPERTY)]`
 */
interface ConstraintInterface
{
    /**
     * Constraint code
     *
     * Should be unique for each constraint.
     * Constraint code of this library must be a UUID v5, using this code as namespace and simple class name as name.
     *
     * For example, a constraint `Foo` must have the code `e9ecc757-fa94-5487-8468-2917e92cae21`
     *
     * @see FieldError::$code
     */
    public const CODE = 'bb8ebf72-1310-4d65-bdb5-9192708543ee';

    /**
     * Get the related constraint validator
     * Can be $this in case of {@see SelfValidatedConstraint}
     *
     * @param RegistryInterface $registry
     *
     * @return ConstraintValidatorInterface<static>
     *
     * @see RegistryInterface::getValidator()
     */
    public function getValidator(RegistryInterface $registry): ConstraintValidatorInterface;
}
