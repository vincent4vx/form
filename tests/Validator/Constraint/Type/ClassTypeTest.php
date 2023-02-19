<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use PHPUnit\Framework\TestCase;

class ClassTypeTest extends TestCase
{
    public function test_check(): void
    {
        $type = new ClassType(\DateTime::class);

        $this->assertTrue($type->check(new \DateTime()));
        $this->assertFalse($type->check(new \stdClass()));
    }

    public function test_generateCheck(): void
    {
        $type = new ClassType(\DateTime::class);

        $this->assertSame('$value instanceof \DateTime', $type->generateCheck('$value'));
    }

    public function test_name()
    {
        $type = new ClassType(\DateTime::class);

        $this->assertSame(\DateTime::class, $type->name());
    }
}
