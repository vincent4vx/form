<?php

namespace Quatrevieux\Form\Validator\Generator;

/**
 * Type for generate a validator expression
 *
 * @see ConstraintValidatorGeneratorInterface::generate()
 */
interface FieldErrorExpressionInterface
{
    /**
     * The expression only returns a single FieldError
     */
    public const RETURN_TYPE_SINGLE = 1;

    /**
     * The expression only returns an array of FieldError
     */
    public const RETURN_TYPE_AGGREGATE = 2;

    /**
     * The expression returns a single FieldError or an array of FieldError
     * Use this if you don't know if the return type of the expression
     */
    public const RETURN_TYPE_BOTH = 3;

    /**
     * Generates the validator expression
     *
     * @param string $fieldAccessor PHP expression use to access to the field value. Ex: '($data->foo ?? null)'
     *
     * @return string The generated PHP expression
     */
    public function generate(string $fieldAccessor): string;

    /**
     * Returns the return type of the expression
     * This value may be combined with bitwise OR operator
     *
     * @return FieldErrorExpression::RETURN_TYPE_* One of the RETURN_TYPE_* constant
     */
    public function returnType(): int;
}
