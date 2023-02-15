<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class IdenticalToTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(IdenticalTo::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'IdenticalTo')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(IdenticalToTestRequest::class) : $this->runtimeForm(IdenticalToTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '42'])->valid());
        $this->assertErrors(['value' => new FieldError('The value should be same as {{ value }}.', ['value' => 42], IdenticalTo::CODE)], $form->submit(['value' => '56'])->errors());

        $this->assertFalse($form->submit(['value' => '40'])->valid());

        $this->assertFalse($form->submit(['typeNotMatching' => 12])->valid());
        $this->assertFalse($form->submit(['typeNotMatching' => 12.0])->valid());
        $this->assertFalse($form->submit(['typeNotMatching' => 12.1])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new IdenticalTo(value: 'bar');

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !(($data->foo ?? null) === \'bar\') ? new \Quatrevieux\Form\Validator\FieldError(\'The value should be same as {{ value }}.\', [\'value\' => \'bar\'], \'8072727f-84e9-580d-9abb-950ca33b4d55\') : null', $constraint);
    }
}

class IdenticalToTestRequest
{
    #[IdenticalTo(42)]
    public ?int $value;

    #[IdenticalTo(12)]
    public ?float $typeNotMatching;
}
