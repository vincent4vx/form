<?php

namespace Quatrevieux\Form\View;

use PHPUnit\Framework\TestCase;

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
}
