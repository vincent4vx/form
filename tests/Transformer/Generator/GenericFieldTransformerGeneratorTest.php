<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Attribute;
use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\SelfValidatedConstraint;
use Quatrevieux\Form\Validator\FieldError;

class GenericFieldTransformerGeneratorTest extends FormTestCase
{
    public function test_generate()
    {
        $this->assertSame('(new \Quatrevieux\Form\Transformer\Generator\MyCustomTransformer(foo: 5))->transformToHttp($data["foo"] ?? null)', (new GenericFieldTransformerGenerator())->generateTransformToHttp(new MyCustomTransformer(5), '$data["foo"] ?? null'));
        $this->assertSame('(new \Quatrevieux\Form\Transformer\Generator\MyCustomTransformer(foo: 5))->transformFromHttp($data["foo"] ?? null)', (new GenericFieldTransformerGenerator())->generateTransformFromHttp(new MyCustomTransformer(5), '$data["foo"] ?? null'));
    }

    public function test_functional()
    {
        $form = $this->generatedForm(TestRequestWithGenericTransformer::class);

        $this->assertSame(8, $form->submit(['foo' => '5'])->value()->foo);

        $o = new TestRequestWithGenericTransformer;
        $o->foo = 42;

        $this->assertSame(['foo' => 39], $form->import($o)->httpValue());
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyCustomTransformer implements FieldTransformerInterface
{
    public function __construct(
        public int $foo,
    ) {
    }

    public function transformFromHttp(mixed $value): mixed
    {
        return $value + $this->foo;
    }

    public function transformToHttp(mixed $value): mixed
    {
        return $value - $this->foo;
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        return false;
    }
}

class TestRequestWithGenericTransformer
{
    #[MyCustomTransformer(3)]
    public ?int $foo;
}
