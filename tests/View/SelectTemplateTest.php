<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\FormTestCase;

class SelectTemplateTest extends FormTestCase
{
    public function test_render_Select()
    {
        $view = new FieldView('name', 'value', null, ['class' => 'foo', 'required' => true], [
            new ChoiceView('value1', 'label1'),
            new ChoiceView('value2', 'label2', true),
        ]);

        $this->assertSame(
            '<select name="name" class="foo" required ><option value="value1" >label1</option><option value="value2" selected>label2</option></select>',
            $view->render(SelectTemplate::Select)
        );
    }

    public function test_render_Radio()
    {
        $view = new FieldView('name', 'value', null, ['class' => 'foo'], [
            new ChoiceView('value1', 'label1'),
            new ChoiceView('value2', 'label2', true),
        ]);

        $this->assertSame(
            '<div class="foo" ><label><input type="radio" name="name" value="value1" >label1</label><label><input type="radio" name="name" value="value2" checked>label2</label><div>',
            $view->render(SelectTemplate::Radio)
        );
    }

    public function test_render_Checkbox()
    {
        $view = new FieldView('name', 'value', null, ['class' => 'foo'], [
            new ChoiceView('value1', 'label1'),
            new ChoiceView('value2', 'label2', true),
        ]);

        $this->assertSame(
            '<div class="foo" ><label><input type="checkbox" name="name" value="value1" >label1</label><label><input type="checkbox" name="name" value="value2" checked>label2</label><div>',
            $view->render(SelectTemplate::Checkbox)
        );
    }
}
