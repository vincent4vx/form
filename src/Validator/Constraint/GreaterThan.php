<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;

/**
 * Check that the field value is greater than the given value
 *
 * Numeric and string values are supported.
 * To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     #[GreaterThan(10)]
 *     public int $foo;
 * }
 * </code>
 *
 * @see GreaterThanOrEqual for a greater or equal comparison
 * @see LessThanOrEqual for the opposite constraint
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class GreaterThan extends AbstractComparisonConstraint
{
    public const CODE = '53c005df-2c8c-5bd6-9fcf-923c82500a9d';

    public function __construct(int|float|string|bool $value, string $message = 'The value should be greater than {{ value }}.')
    {
        parent::__construct($value, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function compare(float|bool|int|string $actual, float|bool|int|string $expected): bool
    {
        return $actual > $expected;
    }

    /**
     * {@inheritdoc}
     */
    protected function operator(): string
    {
        return '>';
    }
}
