<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class LessThanTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(LessThan::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'LessThan')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(LessThanTestRequest::class) : $this->runtimeForm(LessThanTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '40'])->valid());
        $this->assertErrors(['value' => new FieldError('The value should be less than {{ value }}.', ['value' => 42], LessThan::CODE)], $form->submit(['value' => '56'])->errors());

        $this->assertFalse($form->submit(['value' => '42'])->valid());
        $this->assertTrue($form->submit(['value' => '5'])->valid());

        $this->assertTrue($form->submit(['withString' => 'abc'])->valid());
        $this->assertFalse($form->submit(['withString' => 'def'])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new LessThan(value: 'bar');

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !(($data->foo ?? null) < \'bar\') ? new \Quatrevieux\Form\Validator\FieldError(\'The value should be less than {{ value }}.\', [\'value\' => \'bar\'], \'4b394c37-65be-5f85-8972-347b98d7bc0a\') : null', $constraint);
    }
}

class LessThanTestRequest
{
    #[LessThan(42)]
    public ?int $value;

    #[LessThan('ddd')]
    public ?string $withString;
}
