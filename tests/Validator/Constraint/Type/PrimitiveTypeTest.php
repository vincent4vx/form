<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use PHPUnit\Framework\TestCase;

class PrimitiveTypeTest extends TestCase
{
    public function test_name()
    {
        $this->assertSame('int', PrimitiveType::Int->name());
        $this->assertSame('float', PrimitiveType::Float->name());
        $this->assertSame('string', PrimitiveType::String->name());
        $this->assertSame('bool', PrimitiveType::Bool->name());
        $this->assertSame('object', PrimitiveType::Object->name());
        $this->assertSame('array', PrimitiveType::Array->name());
        $this->assertSame('mixed', PrimitiveType::Mixed->name());
        $this->assertSame('null', PrimitiveType::Null->name());
        $this->assertSame('true', PrimitiveType::True->name());
        $this->assertSame('false', PrimitiveType::False->name());
    }

    public function test_check()
    {
        $this->assertTrue(PrimitiveType::Int->check(1));
        $this->assertTrue(PrimitiveType::Float->check(1.0));
        $this->assertTrue(PrimitiveType::String->check('1'));
        $this->assertTrue(PrimitiveType::Bool->check(true));
        $this->assertTrue(PrimitiveType::Object->check(new \stdClass()));
        $this->assertTrue(PrimitiveType::Array->check([]));
        $this->assertTrue(PrimitiveType::Mixed->check(1));
        $this->assertTrue(PrimitiveType::Null->check(null));
        $this->assertTrue(PrimitiveType::True->check(true));
        $this->assertTrue(PrimitiveType::False->check(false));

        $this->assertFalse(PrimitiveType::Int->check(1.0));
        $this->assertFalse(PrimitiveType::Float->check(1));
        $this->assertFalse(PrimitiveType::String->check(1));
        $this->assertFalse(PrimitiveType::Bool->check(1));
        $this->assertFalse(PrimitiveType::Object->check(1));
        $this->assertFalse(PrimitiveType::Array->check(1));
        $this->assertFalse(PrimitiveType::Null->check(1));
        $this->assertFalse(PrimitiveType::True->check(1));
        $this->assertFalse(PrimitiveType::False->check(1));
    }

    public function test_generateCheck()
    {
        $this->assertSame('is_int($value)', PrimitiveType::Int->generateCheck('$value'));
        $this->assertSame('is_float($value)', PrimitiveType::Float->generateCheck('$value'));
        $this->assertSame('is_string($value)', PrimitiveType::String->generateCheck('$value'));
        $this->assertSame('is_bool($value)', PrimitiveType::Bool->generateCheck('$value'));
        $this->assertSame('is_object($value)', PrimitiveType::Object->generateCheck('$value'));
        $this->assertSame('is_array($value)', PrimitiveType::Array->generateCheck('$value'));
        $this->assertSame('true', PrimitiveType::Mixed->generateCheck('$value'));
        $this->assertSame('$value === null', PrimitiveType::Null->generateCheck('$value'));
        $this->assertSame('$value === true', PrimitiveType::True->generateCheck('$value'));
        $this->assertSame('$value === false', PrimitiveType::False->generateCheck('$value'));
    }
}
