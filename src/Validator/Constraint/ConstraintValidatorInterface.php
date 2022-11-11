<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\Validator\FieldError;

/**
 * @template C as ConstraintInterface
 */
interface ConstraintValidatorInterface
{
    /**
     * @param C $constraint
     * @param mixed $value
     * @return FieldError|null
     */
    public function validate(ConstraintInterface $constraint, mixed $value): ?FieldError;
}
