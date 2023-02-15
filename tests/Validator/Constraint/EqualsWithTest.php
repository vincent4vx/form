<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormInterface;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Ramsey\Uuid\Uuid;

class EqualsWithTest extends FormTestCase
{
    private FormInterface $form;
    private FormInterface $generatedForm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->form = $this->runtimeForm(EqualsWithTestRequest::class);
        $this->generatedForm = $this->generatedForm(EqualsWithTestRequest::class);
    }

    public function test_code()
    {
        $this->assertSame(EqualsWith::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'EqualsWith')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['foo' => 'abc', 'bar' => 'abc'])->valid());
        $this->assertErrors(['foo' => new FieldError('Two fields are different', ['field' => 'bar'], EqualsWith::CODE)], $form->submit(['foo' => 'abc', 'bar' => 'bcd'])->errors());
        $this->assertErrors(['foo' => new FieldError('Two fields are different', ['field' => 'bar'], EqualsWith::CODE)], $form->submit(['foo' => ''])->errors());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_strict_comparison(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['other' => 123, 'strict' => 123, 'notStrict' => 123])->valid());
        $this->assertErrors(['strict' => new FieldError('Two fields are different', ['field' => 'other'], EqualsWith::CODE)], $form->submit(['other' => ''])->errors());
        $this->assertErrors(['strict' => new FieldError('Two fields are different', ['field' => 'other'], EqualsWith::CODE), 'notStrict' => 'Two fields are different'], $form->submit(['other' => 123, 'strict' => 456, 'notStrict' => 789])->errors());
        $this->assertErrors(['strict' => new FieldError('Two fields are different', ['field' => 'other'], EqualsWith::CODE)], $form->submit(['other' => '123', 'strict' => 123, 'notStrict' => 123])->errors());
    }

    public function test_generated_code()
    {
        $strict = new EqualsWith('bar', 'my error', true);
        $notStrict = new EqualsWith('bar', 'my error', false);

        $this->assertGeneratedValidator('($data->foo ?? null) !== ($data->bar ?? null) ? new FieldError(\'my error\', [\'field\' => \'bar\'], \'35ef0ca6-ee68-5f99-a87d-b2f635ea4a4a\') : null', $strict);
        $this->assertGeneratedValidator('($data->foo ?? null) != ($data->bar ?? null) ? new FieldError(\'my error\', [\'field\' => \'bar\'], \'35ef0ca6-ee68-5f99-a87d-b2f635ea4a4a\') : null', $notStrict);
    }
}

class EqualsWithTestRequest
{
    #[EqualsWith('bar')]
    public ?string $foo;
    public ?string $bar;

    public $other;

    #[EqualsWith('other')]
    public $strict;

    #[EqualsWith('other', strict: false)]
    public $notStrict;
}
