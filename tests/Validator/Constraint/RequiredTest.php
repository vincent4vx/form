<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

class RequiredTest extends FormTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->form = $this->runtimeForm(RequiredTestRequest::class);
        $this->generatedForm = $this->generatedForm(RequiredTestRequest::class);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $this->assertFalse($form->submit([])->valid());
        $this->assertFalse($form->submit(['value' => ''])->valid());
        $this->assertFalse($form->submit(['value' => []])->valid());

        $this->assertTrue($form->submit(['value' => ' '])->valid());
        $this->assertTrue($form->submit(['value' => 'a'])->valid());
        $this->assertTrue($form->submit(['value' => 0])->valid());
        $this->assertTrue($form->submit(['value' => 42])->valid());
        $this->assertTrue($form->submit(['value' => 0.0])->valid());
        $this->assertTrue($form->submit(['value' => false])->valid());
        $this->assertTrue($form->submit(['value' => ['foo']])->valid());
    }

    public function test_generated_code()
    {
        $generator = new ValidatorGenerator(new NullConstraintValidatorRegistry());
        $defaultMessage = new Required();
        $this->assertEquals('($data->field ?? null) === null || ($data->field ?? null) === \'\' || ($data->field ?? null) === [] ? new FieldError(\'This value is required\') : null', $defaultMessage->generate($defaultMessage, '($data->field ?? null)', $generator));

        $customMessage = new Required('my error');
        $this->assertEquals('($data->field ?? null) === null || ($data->field ?? null) === \'\' || ($data->field ?? null) === [] ? new FieldError(\'my error\') : null', $customMessage->generate($customMessage, '($data->field ?? null)', $generator));
    }
}

class RequiredTestRequest
{
    #[Required('my error')]
    public mixed $value;
}
