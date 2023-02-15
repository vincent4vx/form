<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;

/**
 * Check that the field value is equal to the given value
 * This comparison use the simple comparison operator (!=) and not the strict one (!==).
 *
 * Numeric and string values are supported.
 * To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     #[NotEqualTo(10)]
 *     public int $foo;
 * }
 * </code>
 *
 * @see NotIdenticalTo for a strict comparison
 * @see EqualTo for the opposite constraint
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class NotEqualTo extends AbstractComparisonConstraint
{
    public const CODE = '64ea4d23-adda-57cc-81eb-fdfe82610ec9';

    public function __construct(int|float|string|bool $value, string $message = 'The value should not be equal to {{ value }}.')
    {
        parent::__construct($value, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function compare(float|bool|int|string $actual, float|bool|int|string $expected): bool
    {
        return $actual != $expected;
    }

    /**
     * {@inheritdoc}
     */
    protected function operator(): string
    {
        return '!=';
    }
}
