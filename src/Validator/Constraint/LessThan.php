<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;

/**
 * Check that the field value is less than the given value
 *
 * Numeric and string values are supported.
 * To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     #[LessThan(10)]
 *     public int $foo;
 * }
 * </code>
 *
 * @see LessThanOrEqual for a less or equal comparison
 * @see GreaterThanOrEqual for the opposite constraint
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class LessThan extends AbstractComparisonConstraint
{
    public const CODE = '4b394c37-65be-5f85-8972-347b98d7bc0a';

    public function __construct(int|float|string|bool $value, string $message = 'The value should be less than {{ value }}.')
    {
        parent::__construct($value, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function compare(float|bool|int|string $actual, float|bool|int|string $expected): bool
    {
        return $actual < $expected;
    }

    /**
     * {@inheritdoc}
     */
    protected function operator(): string
    {
        return '<';
    }
}
