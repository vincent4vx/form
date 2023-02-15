<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class LessThanOrEqualTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(LessThanOrEqual::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'LessThanOrEqual')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(LessThanOrEqualTestRequest::class) : $this->runtimeForm(LessThanOrEqualTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '40'])->valid());
        $this->assertErrors(['value' => new FieldError('The value should be less than or equal to {{ value }}.', ['value' => 42], LessThanOrEqual::CODE)], $form->submit(['value' => '56'])->errors());

        $this->assertTrue($form->submit(['value' => '42'])->valid());
        $this->assertTrue($form->submit(['value' => '5'])->valid());

        $this->assertTrue($form->submit(['withString' => 'abc'])->valid());
        $this->assertFalse($form->submit(['withString' => 'def'])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new LessThanOrEqual(value: 'bar');

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && !(($data->foo ?? null) <= \'bar\') ? new \Quatrevieux\Form\Validator\FieldError(\'The value should be less than or equal to {{ value }}.\', [\'value\' => \'bar\'], \'00ca521f-5da3-5336-8469-20d7e571c2dc\') : null', $constraint);
    }
}

class LessThanOrEqualTestRequest
{
    #[LessThanOrEqual(42)]
    public ?int $value;

    #[LessThanOrEqual('ddd')]
    public ?string $withString;
}
