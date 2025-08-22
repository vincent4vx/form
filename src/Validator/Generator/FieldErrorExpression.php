<?php

namespace Quatrevieux\Form\Validator\Generator;

use Closure;

/**
 * Base FieldError expression generator
 */
final class FieldErrorExpression implements FieldErrorExpressionInterface
{
    public function __construct(
        /**
         * Code generator
         * Takes as parameter the field accessor and returns the PHP expression
         *
         * @var Closure(string): string
         */
        private readonly Closure $generator,

        /**
         * Return type of the expression
         *
         * @var FieldErrorExpressionInterface::RETURN_TYPE_*
         */
        private readonly int $returnType = self::RETURN_TYPE_BOTH,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function generate(string $fieldAccessor): string
    {
        return ($this->generator)($fieldAccessor);
    }

    /**
     * {@inheritdoc}
     */
    public function returnType(): int
    {
        return $this->returnType;
    }

    /**
     * Returns a FieldErrorExpression that always returns a single FieldError
     *
     * @param Closure(string): string $generator Code generator
     * @return FieldErrorExpression
     *
     * @see FieldErrorExpressionInterface::RETURN_TYPE_SINGLE
     */
    public static function single(Closure $generator): self
    {
        return new self($generator, self::RETURN_TYPE_SINGLE);
    }

    /**
     * Returns a FieldErrorExpression that always returns an array of FieldError
     *
     * @param Closure(string): string $generator Code generator
     * @return FieldErrorExpression
     *
     * @see FieldErrorExpressionInterface::RETURN_TYPE_AGGREGATE
     */
    public static function aggregate(Closure $generator): self
    {
        return new self($generator, self::RETURN_TYPE_AGGREGATE);
    }

    /**
     * Returns a FieldErrorExpression that can return a single FieldError or an array of FieldError (or undefined)
     *
     * @param Closure(string): string $generator Code generator
     * @return FieldErrorExpression
     *
     * @see FieldErrorExpressionInterface::RETURN_TYPE_BOTH
     */
    public static function undefined(Closure $generator): self
    {
        return new self($generator, self::RETURN_TYPE_BOTH);
    }
}
