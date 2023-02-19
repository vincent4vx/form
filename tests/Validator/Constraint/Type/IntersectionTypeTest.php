<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use PHPUnit\Framework\TestCase;

class IntersectionTypeTest extends TestCase
{
    public function test_check()
    {
        $type = new IntersectionType([
            new ClassType(A::class),
            new ClassType(B::class),
        ]);

        $this->assertTrue($type->check(new AB()));
        $this->assertFalse($type->check(new AC()));
        $this->assertFalse($type->check(new \stdClass()));
    }

    public function test_name()
    {
        $type = new IntersectionType([
            new ClassType(A::class),
            new ClassType(B::class),
        ]);

        $this->assertSame(A::class . '&' . B::class, $type->name());
    }

    public function test_generateCheck()
    {
        $type = new IntersectionType([
            new ClassType(A::class),
            new ClassType(B::class),
        ]);

        $this->assertSame('($value instanceof \\' . A::class . ') && ($value instanceof \\' . B::class . ')', $type->generateCheck('$value'));
    }
}

interface A {}
interface B {}
interface C {}
class AB implements A, B {}
class AC implements A, C {}
