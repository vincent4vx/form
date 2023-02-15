<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class NotIdenticalToTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(NotIdenticalTo::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'NotIdenticalTo')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(NotIdenticalToTestRequest::class) : $this->runtimeForm(NotIdenticalToTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '56'])->valid());
        $this->assertErrors(['value' => new FieldError('The value should not be same as {{ value }}.', ['value' => 42], NotIdenticalTo::CODE)], $form->submit(['value' => '42'])->errors());

        $this->assertTrue($form->submit(['value' => '40'])->valid());

        $this->assertTrue($form->submit(['typeNotMatching' => 12])->valid());
        $this->assertTrue($form->submit(['typeNotMatching' => 12.0])->valid());
        $this->assertTrue($form->submit(['typeNotMatching' => 12.1])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new NotIdenticalTo(value: 'bar');

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !(($data->foo ?? null) !== \'bar\') ? new \Quatrevieux\Form\Validator\FieldError(\'The value should not be same as {{ value }}.\', [\'value\' => \'bar\'], \'9ca21c2d-bea0-5848-a218-91eb6cabd3f9\') : null', $constraint);
    }
}

class NotIdenticalToTestRequest
{
    #[NotIdenticalTo(42)]
    public ?int $value;

    #[NotIdenticalTo(12)]
    public ?float $typeNotMatching;
}
