<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class NotEqualToTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(NotEqualTo::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'NotEqualTo')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(NotEqualToTestRequest::class) : $this->runtimeForm(NotEqualToTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '56'])->valid());
        $this->assertErrors(['value' => new FieldError('The value should not be equal to {{ value }}.', ['value' => 42], NotEqualTo::CODE)], $form->submit(['value' => '42'])->errors());

        $this->assertTrue($form->submit(['value' => '40'])->valid());

        $this->assertFalse($form->submit(['typeNotMatching' => 12])->valid());
        $this->assertFalse($form->submit(['typeNotMatching' => 12.0])->valid());
        $this->assertTrue($form->submit(['typeNotMatching' => 12.1])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new NotEqualTo(value: 'bar');

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !(($data->foo ?? null) != \'bar\') ? new \Quatrevieux\Form\Validator\FieldError(\'The value should not be equal to {{ value }}.\', [\'value\' => \'bar\'], \'64ea4d23-adda-57cc-81eb-fdfe82610ec9\') : null', $constraint);
    }
}

class NotEqualToTestRequest
{
    #[NotEqualTo(42)]
    public ?int $value;

    #[NotEqualTo(12)]
    public ?float $typeNotMatching;
}
