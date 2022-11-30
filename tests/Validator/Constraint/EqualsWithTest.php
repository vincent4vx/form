<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormInterface;
use Quatrevieux\Form\FormTestCase;

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

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['foo' => 'abc', 'bar' => 'abc'])->valid());
        $this->assertEquals(['foo' => 'Two fields are different'], $form->submit(['foo' => 'abc', 'bar' => 'bcd'])->errors());
        $this->assertEquals(['foo' => 'Two fields are different'], $form->submit(['foo' => ''])->errors());
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
        $this->assertEquals(['strict' => 'Two fields are different'], $form->submit(['other' => ''])->errors());
        $this->assertEquals(['strict' => 'Two fields are different', 'notStrict' => 'Two fields are different'], $form->submit(['other' => 123, 'strict' => 456, 'notStrict' => 789])->errors());
        $this->assertEquals(['strict' => 'Two fields are different'], $form->submit(['other' => '123', 'strict' => 123, 'notStrict' => 123])->errors());
    }

    public function test_generated_code()
    {
        $strict = new EqualsWith('foo', 'my error', true);
        $notStrict = new EqualsWith('foo', 'my error', false);

        $this->assertSame('($data->bar ?? null) !== ($data->foo ?? null) ? new FieldError(\'my error\') : null', $strict->generate($strict, '($data->bar ?? null)'));
        $this->assertSame('($data->bar ?? null) != ($data->foo ?? null) ? new FieldError(\'my error\') : null', $notStrict->generate($notStrict, '($data->bar ?? null)'));
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