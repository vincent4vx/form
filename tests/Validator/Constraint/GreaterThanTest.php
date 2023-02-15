<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class GreaterThanTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(GreaterThan::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'GreaterThan')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(GreaterThanTestRequest::class) : $this->runtimeForm(GreaterThanTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '56'])->valid());
        $this->assertErrors(['value' => new FieldError('The value should be greater than {{ value }}.', ['value' => 42], GreaterThan::CODE)], $form->submit(['value' => '40'])->errors());

        $this->assertFalse($form->submit(['value' => '42'])->valid());
        $this->assertTrue($form->submit(['value' => '100'])->valid());

        $this->assertTrue($form->submit(['withString' => 'def'])->valid());
        $this->assertFalse($form->submit(['withString' => 'abc'])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new GreaterThan(value: 'bar');

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !(($data->foo ?? null) > \'bar\') ? new \Quatrevieux\Form\Validator\FieldError(\'The value should be greater than {{ value }}.\', [\'value\' => \'bar\'], \'53c005df-2c8c-5bd6-9fcf-923c82500a9d\') : null', $constraint);
    }
}

class GreaterThanTestRequest
{
    #[GreaterThan(42)]
    public ?int $value;

    #[GreaterThan('ddd')]
    public ?string $withString;
}
