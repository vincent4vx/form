<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\ValidatorInterface;

/**
 * Type for constraint validator expression generator
 * This interface should be implemented by {@see ValidatorInterface} class
 *
 * @template C as ConstraintInterface
 */
interface ConstraintValidatorGeneratorInterface
{
    /**
     * Generate the validator expression.
     *
     * This expression must return a FieldError or null value
     * Use `$generator` parameter to generate sub-constraint validator expression
     *
     * Ex: A constraint which validate that the value contains only alpha chars will generate an expression like :
     * '!is_string($data->foo ?? null) || !ctype_alpha($data->foo ?? null) ? new FieldError("my error message") : null'
     *
     * @param C $constraint Constraint instance
     * @param ValidatorGenerator $generator Code generator instance
     *
     * @return FieldErrorExpressionInterface PHP expression
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface;
}
