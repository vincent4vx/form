<?php

namespace Quatrevieux\Form\Validator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\Fixtures\RequestWithDefaultValue;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Transformer\Field\DefaultValue;
use Quatrevieux\Form\Validator\Constraint\GreaterThan;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\Required;

use function get_class;

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

    public function test_create_with_default_values_on_properties_should_remove_required_constraint()
    {
        $factory = new RuntimeValidatorFactory(new DefaultRegistry());

        $this->assertEquals([], $factory->create(RequestWithDefaultValue::class)->fieldsConstraints);
    }

    public function test_create_with_default_value_attribute_should_remove_required_constraint()
    {
        $factory = new RuntimeValidatorFactory(new DefaultRegistry());
        $req = new class {
            #[DefaultValue(21), GreaterThan(0)]
            public int $foo;

            #[DefaultValue('foo')]
            public string $bar;
        };

        $this->assertEquals([
            'foo' => [new GreaterThan(0)],
        ], $factory->create(get_class($req))->fieldsConstraints);
    }
}
