<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class EqualToTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(EqualTo::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'EqualTo')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(EqualToTestRequest::class) : $this->runtimeForm(EqualToTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '42'])->valid());
        $this->assertErrors(['value' => new FieldError('The value should be equal to {{ value }}.', ['value' => 42], EqualTo::CODE)], $form->submit(['value' => '56'])->errors());

        $this->assertFalse($form->submit(['value' => '40'])->valid());

        $this->assertTrue($form->submit(['typeNotMatching' => 12])->valid());
        $this->assertTrue($form->submit(['typeNotMatching' => 12.0])->valid());
        $this->assertFalse($form->submit(['typeNotMatching' => 12.1])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new EqualTo(value: 'bar');

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !(($data->foo ?? null) == \'bar\') ? new \Quatrevieux\Form\Validator\FieldError(\'The value should be equal to {{ value }}.\', [\'value\' => \'bar\'], \'10a69fac-d049-55d0-af88-121872ef9892\') : null', $constraint);
    }
}

class EqualToTestRequest
{
    #[EqualTo(42)]
    public ?int $value;

    #[EqualTo(12)]
    public ?float $typeNotMatching;
}
