<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;

/**
 * Check that the field value is less than or equal to the given value
 *
 * Numeric and string values are supported.
 * To ensure that the comparison is done in the same type, add a typehint to the field and use the same type on the constraint's value.
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     #[LessThanOrEqual(10)]
 *     public int $foo;
 * }
 * </code>
 *
 * @see LessThan for a less without equal comparison
 * @see GreaterThan for the opposite constraint
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class LessThanOrEqual extends AbstractComparisonConstraint
{
    public const CODE = '00ca521f-5da3-5336-8469-20d7e571c2dc';

    public function __construct(int|float|string|bool $value, string $message = 'The value should be less than or equal to {{ value }}.')
    {
        parent::__construct($value, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function compare(float|bool|int|string $actual, float|bool|int|string $expected): bool
    {
        return $actual <= $expected;
    }

    /**
     * {@inheritdoc}
     */
    protected function operator(): string
    {
        return '<=';
    }
}
