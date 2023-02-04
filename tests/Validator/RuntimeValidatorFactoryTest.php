<?php

namespace Quatrevieux\Form\Validator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\Required;

class RuntimeValidatorFactoryTest extends TestCase
{
    public function test_create_without_constraints()
    {
        $factory = new RuntimeValidatorFactory(new DefaultRegistry());

        $this->assertInstanceOf(RuntimeValidator::class, $factory->create(SimpleRequest::class));
        $this->assertEmpty($factory->create(SimpleRequest::class)->fieldsConstraints);
    }

    public function test_create_with_constraints()
    {
        $factory = new RuntimeValidatorFactory(new DefaultRegistry());

        $this->assertEquals([
            'foo' => [new Required()],
            'bar' => [new Required('bar must be set'), new Length(min: 3)],
        ], $factory->create(RequiredParametersRequest::class)->fieldsConstraints);
    }
}
