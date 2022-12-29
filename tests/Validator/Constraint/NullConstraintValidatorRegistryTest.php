<?php

namespace Quatrevieux\Form\Validator\Constraint;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\DummyTranslator;

class NullConstraintValidatorRegistryTest extends TestCase
{
    public function test_get()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot use external validator : no container or custom registry defined.');
        $registry = new NullConstraintValidatorRegistry();

        $this->assertNull($registry->getValidator(Length::class));
    }

    public function test_getTranslator()
    {
        $registry = new NullConstraintValidatorRegistry();
        $this->assertSame(DummyTranslator::instance(), $registry->getTranslator());
    }
}
