<?php

namespace Quatrevieux\Form\View;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class FieldViewTest extends TestCase
{
    public function test_render()
    {
        $view = new FieldView(
            'name',
            'my value',
            null,
            [
                'type' => 'text',
                'required' => true,
            ]
        );

        $this->assertSame('<input name="name" value="my value" type="text" required />', $view->render(FieldTemplate::Input));
        $this->assertSame('<textarea name="name" type="text" required >my value</textarea>', $view->render(FieldTemplate::Textarea));
    }

    public function test_toString()
    {
        $view = new FieldView(
            'name',
            'my value',
            null,
            [
                'type' => 'text',
                'required' => true,
            ]
        );

        $this->assertSame('<input name="name" value="my value" type="text" required />', (string) $view);
    }

    public function test_functional_builder_api()
    {
        $view = new FieldView('name', 'my value', null);

        $this->assertSame('<input name="name" value="my value" required type="text" placeholder="my placeholder" />', (string) $view->is('required')->type('text')->attr('placeholder', 'my placeholder'));
    }

    public function test_class()
    {
        $view = new FieldView('name', 'my value', null);

        $this->assertSame('<input name="name" value="my value" class="foo" />', (string) $view->class('foo'));
        $this->assertSame('<input name="name" value="my value" class="foo bar" />', (string) $view->class('bar'));
    }

    public function test_choices()
    {
        $view = new FieldView('name', 'my value', null, []);

        $choices = [
            new ChoiceView('value1', 'label1'),
            new ChoiceView('value2', 'label2'),
        ];

        $this->assertSame($view, $view->choices($choices));
        $this->assertSame($choices, $view->choices);
    }

    public function test_choices_with_translator()
    {
        $view = new FieldView('name', 'my value', null, []);
        $translator = $this->createMock(TranslatorInterface::class);

        $choices = [
            new ChoiceView('value1', 'label1'),
            new ChoiceView('value2', 'label2'),
        ];

        $this->assertSame($view, $view->choices($choices, $translator));
        $this->assertSame($choices, $view->choices);

        $translator->method('trans')->willReturnMap([
            ['label1', [], null, null, 'translated1'],
            ['label2', [], null, null, 'translated2'],
        ]);

        $this->assertSame('translated1', $view->choices[0]->localizedLabel());
        $this->assertSame('translated2', $view->choices[1]->localizedLabel());
    }
}
