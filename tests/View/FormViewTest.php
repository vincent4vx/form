<?php

namespace Quatrevieux\Form\View;

use PHPUnit\Framework\TestCase;

class FormViewTest extends TestCase
{
    public function test_array_access()
    {
        $form = new FormView(
            ['foo' => new FieldView('foo', 'bar', null, [])],
            ['foo' => 'bar'],
        );

        $this->assertTrue(isset($form['foo']));
        $this->assertFalse(isset($form['bar']));
        $this->assertSame($form->fields['foo'], $form['foo']);
        $this->assertCount(1, $form);
    }

    public function test_array_access_set_not_allowed()
    {
        $this->expectException(\BadMethodCallException::class);
        $form = new FormView(
            ['foo' => new FieldView('foo', 'bar', null, [])],
            ['foo' => 'bar'],
        );

        $form['foo'] = 'bar';
    }

    public function test_array_access_unset_not_allowed()
    {
        $this->expectException(\BadMethodCallException::class);
        $form = new FormView(
            ['foo' => new FieldView('foo', 'bar', null, [])],
            ['foo' => 'bar'],
        );

        unset($form['foo']);
    }

    public function test_iterator()
    {
        $form = new FormView(
            [
                'foo' => new FieldView('foo', 'bar', null, []),
                'bar' => new FieldView('bar', null, null, []),
            ],
            ['foo' => 'bar'],
        );

        $this->assertSame($form->fields, iterator_to_array($form));
    }
}
