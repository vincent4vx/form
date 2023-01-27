<?php

namespace Quatrevieux\Form\View\Provider;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\View\FieldView;

class FieldViewConfigurationTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_view(bool $generated)
    {
        $form = $generated ? $this->generatedForm(FormWithFieldViewConfiguration::class) : $this->runtimeForm(FormWithFieldViewConfiguration::class);

        $view = $form->view();

        $this->assertEquals(new FieldView('number', null, null, ['min' => 0, 'max' => 100, 'step' => 5, 'type' => 'number']), $view->fields['number']);
        $this->assertEquals(new FieldView('withDefault', 'foo', null, []), $view->fields['withDefault']);
        $this->assertEquals(new FieldView('withIdAndType', null, null, ['id' => 'foo', 'type' => 'text']), $view->fields['withIdAndType']);

        $view = $form->submit(['number' => '42', 'withDefault' => 'bar', 'withIdAndType' => 'baz'])->view();

        $this->assertEquals(new FieldView('number', 42, null, ['min' => 0, 'max' => 100, 'step' => 5, 'type' => 'number']), $view->fields['number']);
        $this->assertEquals(new FieldView('withDefault', 'bar', null, []), $view->fields['withDefault']);
        $this->assertEquals(new FieldView('withIdAndType', 'baz', null, ['id' => 'foo', 'type' => 'text']), $view->fields['withIdAndType']);
    }

    public function test_generate()
    {
        $generator = (new FieldViewConfiguration());

        $this->assertSame('new \Quatrevieux\Form\View\FieldView(\'foo\', $value["foo"] ?? null, ($__tmp_6fb11202bf82766a881ea3ed253bc52c = $errors["foo"] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6fb11202bf82766a881ea3ed253bc52c : null, [\'min\' => 3, \'required\' => true])', $generator->generateFieldViewExpression(new FieldViewConfiguration(), 'foo', ['min' => 3, 'required' => true])('$value["foo"] ?? null', '$errors["foo"] ?? null', null));
        $this->assertSame('new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", $value["foo"] ?? null, ($__tmp_6fb11202bf82766a881ea3ed253bc52c = $errors["foo"] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6fb11202bf82766a881ea3ed253bc52c : null, [\'min\' => 3, \'required\' => true])', $generator->generateFieldViewExpression(new FieldViewConfiguration(), 'foo', ['min' => 3, 'required' => true])('$value["foo"] ?? null', '$errors["foo"] ?? null', '$rootField'));
        $this->assertSame('new \Quatrevieux\Form\View\FieldView(\'foo\', $value["foo"] ?? null ?? -1, ($__tmp_6fb11202bf82766a881ea3ed253bc52c = $errors["foo"] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6fb11202bf82766a881ea3ed253bc52c : null, [\'min\' => 3, \'required\' => true, \'step\' => 3, \'id\' => \'input_foo\', \'type\' => \'number\'])', $generator->generateFieldViewExpression(new FieldViewConfiguration(type: 'number', id: 'input_foo', defaultValue: -1, attributes: ['step' => 3]), 'foo', ['min' => 3, 'required' => true])('$value["foo"] ?? null', '$errors["foo"] ?? null', null));
    }
}

class FormWithFieldViewConfiguration
{
    #[FieldViewConfiguration(type: 'number', attributes: ['min' => 0, 'max' => 100, 'step' => 5])]
    public ?int $number;

    #[FieldViewConfiguration(defaultValue: 'foo')]
    public ?string $withDefault;

    #[FieldViewConfiguration(type: 'text', id: 'foo')]
    public ?string $withIdAndType;
}
