<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormInterface;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class RequiredTest extends FormTestCase
{
    private FormInterface $form;
    private FormInterface $generatedForm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->form = $this->runtimeForm(RequiredTestRequest::class);
        $this->generatedForm = $this->generatedForm(RequiredTestRequest::class);
    }

    public function test_code()
    {
        $this->assertSame(Required::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'Required')->toString());
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
        $this->assertErrors(['value' => new FieldError('my error', [], Required::CODE)], $form->submit(['value' => []])->errors());

        $this->assertTrue($form->submit(['value' => ' '])->valid());
        $this->assertTrue($form->submit(['value' => 'a'])->valid());
        $this->assertTrue($form->submit(['value' => 0])->valid());
        $this->assertTrue($form->submit(['value' => 42])->valid());
        $this->assertTrue($form->submit(['value' => 0.0])->valid());
        $this->assertTrue($form->submit(['value' => false])->valid());
        $this->assertTrue($form->submit(['value' => ['foo']])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_field_view(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $this->assertEquals('<input name="value" value="" required />', $form->view()['value']);
    }

    public function test_generated_code()
    {
        $defaultMessage = new Required();
        $this->assertGeneratedValidator('($data->foo ?? null) === null || ($data->foo ?? null) === \'\' || ($data->foo ?? null) === [] ? new FieldError(\'This value is required\', [], \'b1ac3a70-06db-5cd6-8f0e-8e6b98b3fcb5\') : null', $defaultMessage);

        $customMessage = new Required('my error');
        $this->assertGeneratedValidator('($data->foo ?? null) === null || ($data->foo ?? null) === \'\' || ($data->foo ?? null) === [] ? new FieldError(\'my error\', [], \'b1ac3a70-06db-5cd6-8f0e-8e6b98b3fcb5\') : null', $customMessage);
    }
}

class RequiredTestRequest
{
    #[Required('my error')]
    public mixed $value;
}
