<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use PHPUnit\Framework\TestCase;

class UnionTypeTest extends TestCase
{
    public function test_check()
    {
        $type = new UnionType([
            PrimitiveType::Float,
            PrimitiveType::String,
        ]);

        $this->assertTrue($type->check(1.0));
        $this->assertTrue($type->check('1.0'));
        $this->assertFalse($type->check(new \stdClass()));
        $this->assertFalse($type->check(15));
    }

    public function test_name()
    {
        $type = new UnionType([
            PrimitiveType::Float,
            PrimitiveType::String,
        ]);

        $this->assertSame('float|string', $type->name());
    }

    public function test_generateCheck()
    {
        $type = new UnionType([
            PrimitiveType::Float,
            PrimitiveType::String,
        ]);

        $this->assertSame('(is_float($value)) || (is_string($value))', $type->generateCheck('$value'));
    }
}
