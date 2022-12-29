<?php

namespace Quatrevieux\Form\Validator\Generator;

use PHPUnit\Framework\TestCase;

class FieldErrorExpressionTest extends TestCase
{
    public function test_undefined()
    {
        $expr = FieldErrorExpression::undefined(fn (string $fieldAccessor) => $fieldAccessor . ' === 123 ? new FieldError("error") : null');

        $this->assertSame(FieldErrorExpressionInterface::RETURN_TYPE_BOTH, $expr->returnType());
        $this->assertSame('$data->field === 123 ? new FieldError("error") : null', $expr->generate('$data->field'));
    }

    public function test_single()
    {
        $expr = FieldErrorExpression::single(fn (string $fieldAccessor) => $fieldAccessor . ' === 123 ? new FieldError("error") : null');

        $this->assertSame(FieldErrorExpressionInterface::RETURN_TYPE_SINGLE, $expr->returnType());
        $this->assertSame('$data->field === 123 ? new FieldError("error") : null', $expr->generate('$data->field'));
    }

    public function test_array()
    {
        $expr = FieldErrorExpression::aggregate(fn (string $fieldAccessor) => $fieldAccessor . ' === 123 ? [new FieldError("error")] : null');

        $this->assertSame(FieldErrorExpressionInterface::RETURN_TYPE_AGGREGATE, $expr->returnType());
        $this->assertSame('$data->field === 123 ? [new FieldError("error")] : null', $expr->generate('$data->field'));
    }
}
