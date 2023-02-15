<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;

/**
 * Check that the field value is greater than or equal to the given value
 *
 * Numeric and string values are supported.
 * To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     #[GreaterThanOrEqual(10)]
 *     public int $foo;
 * }
 * </code>
 *
 * @see GreaterThan for a greater without equal comparison
 * @see LessThan for the opposite constraint
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class GreaterThanOrEqual extends AbstractComparisonConstraint
{
    public const CODE = 'fbe34b3a-b434-5047-8fa8-947d1a37583f';

    public function __construct(int|float|string|bool $value, string $message = 'The value should be greater than or equal to {{ value }}.')
    {
        parent::__construct($value, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function compare(float|bool|int|string $actual, float|bool|int|string $expected): bool
    {
        return $actual >= $expected;
    }

    /**
     * {@inheritdoc}
     */
    protected function operator(): string
    {
        return '>=';
    }
}
