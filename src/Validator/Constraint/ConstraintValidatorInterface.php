<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\Validator\FieldError;

/**
 * Type used for validate a value depending on a constraint object
 *
 * @template C as ConstraintInterface
 */
interface ConstraintValidatorInterface
{
    /**
     * Validate the field value, depending on the constraint passed as parameter
     *
     * @param C $constraint Constraint parameters.
     * @param mixed $value Value to validate. null value must be handled by the validator.
     * @param object $data The DTO instance to validate.
     *
     * @return FieldError|mixed[]|null The error, or null if there is no error. If the field is an array or object, the value may be an array of errors.
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): FieldError|array|null;
}
