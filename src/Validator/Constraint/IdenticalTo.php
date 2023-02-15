<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Transformer\Field\Cast;

/**
 * Check that the field value is same as to the given value
 * This comparison use the strict comparison operator (===).
 *
 * Numeric and string values are supported.
 * The value type must be the same as the field type, and the field value must be cast using typehint or {@see Cast} transformer.
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     #[IdenticalTo(10)]
 *     public int $foo;
 * }
 * </code>
 *
 * @see EqualTo for a simple comparison
 * @see NotIdenticalTo for the opposite constraint
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class IdenticalTo extends AbstractComparisonConstraint
{
    public const CODE = '8072727f-84e9-580d-9abb-950ca33b4d55';

    public function __construct(int|float|string|bool $value, string $message = 'The value should be same as {{ value }}.')
    {
        parent::__construct($value, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function compare(float|bool|int|string $actual, float|bool|int|string $expected): bool
    {
        return $actual === $expected;
    }

    /**
     * {@inheritdoc}
     */
    protected function operator(): string
    {
        return '===';
    }
}
