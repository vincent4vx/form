<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class GreaterThanOrEqualTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(GreaterThanOrEqual::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'GreaterThanOrEqual')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(GreaterThanOrEqualTestRequest::class) : $this->runtimeForm(GreaterThanOrEqualTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '56'])->valid());
        $this->assertErrors(['value' => new FieldError('The value should be greater than or equal to {{ value }}.', ['value' => 42], GreaterThanOrEqual::CODE)], $form->submit(['value' => '40'])->errors());

        $this->assertTrue($form->submit(['value' => '42'])->valid());
        $this->assertTrue($form->submit(['value' => '100'])->valid());

        $this->assertTrue($form->submit(['withString' => 'def'])->valid());
        $this->assertFalse($form->submit(['withString' => 'abc'])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new GreaterThanOrEqual(value: 'bar');

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !(($data->foo ?? null) >= \'bar\') ? new \Quatrevieux\Form\Validator\FieldError(\'The value should be greater than or equal to {{ value }}.\', [\'value\' => \'bar\'], \'fbe34b3a-b434-5047-8fa8-947d1a37583f\') : null', $constraint);
    }
}

class GreaterThanOrEqualTestRequest
{
    #[GreaterThanOrEqual(42)]
    public ?int $value;

    #[GreaterThanOrEqual('ddd')]
    public ?string $withString;
}
