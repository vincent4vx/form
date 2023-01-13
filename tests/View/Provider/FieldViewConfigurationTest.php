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
