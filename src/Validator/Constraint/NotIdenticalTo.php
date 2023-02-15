<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;

/**
 * Check that the field value is equal to the given value
 * This comparison use the strict comparison operator (!==).
 *
 * Numeric and string values are supported.
 * The value type must be the same as the field type, and the field value must be cast using typehint or {@see Cast} transformer.
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     #[NotIdenticalTo(10)]
 *     public int $foo;
 * }
 * </code>
 *
 * @see NotEqualTo for a simple comparison
 * @see IdenticalTo for the opposite constraint
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class NotIdenticalTo extends AbstractComparisonConstraint
{
    public const CODE = '9ca21c2d-bea0-5848-a218-91eb6cabd3f9';

    public function __construct(int|float|string|bool $value, string $message = 'The value should not be same as {{ value }}.')
    {
        parent::__construct($value, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function compare(float|bool|int|string $actual, float|bool|int|string $expected): bool
    {
        return $actual !== $expected;
    }

    /**
     * {@inheritdoc}
     */
    protected function operator(): string
    {
        return '!==';
    }
}
