<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use Quatrevieux\Form\Fixtures\EmbeddedForm;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\WithEmbedded;

class SubmittedFormTest extends FormTestCase
{
    public function test_getters()
    {
        $form = $this->runtimeForm(SimpleRequest::class)->submit(['foo' => 'aaa', 'bar' => 'bbb']);

        $this->assertInstanceOf(SimpleRequest::class, $form->value());
        $this->assertSame('aaa', $form->value()->foo);
        $this->assertSame('bbb', $form->value()->bar);
        $this->assertSame(['foo' => 'aaa', 'bar' => 'bbb'], $form->httpValue());
        $this->assertTrue($form->valid());
        $this->assertSame([], $form->errors());
    }

    public function test_getters_with_errors()
    {
        $form = $this->runtimeForm(RequiredParametersRequest::class)->submit([]);

        $this->assertInstanceOf(RequiredParametersRequest::class, $form->value());
        $this->assertFalse(isset($form->value()->foo));
        $this->assertFalse(isset($form->value()->bar));
        $this->assertSame([], $form->httpValue());
        $this->assertFalse($form->valid());
        $this->assertErrors([
            'foo' => 'This value is required',
            'bar' => 'bar must be set',
        ], $form->errors());
    }

    public function test_import_should_only_forward_to_base_form()
    {
        $other = new SimpleRequest();
        $other->foo = 'ccc';
        $other->bar = 'ddd';

        $form = $this->runtimeForm(SimpleRequest::class)->submit(['foo' => 'aaa', 'bar' => 'bbb']);
        $otherForm = $form->import($other);

        $this->assertNotEquals($form, $otherForm);
        $this->assertSame($other, $otherForm->value());
        $this->assertSame(['foo' => 'ccc', 'bar' => 'ddd'], $otherForm->httpValue());
    }

    public function test_submit_should_replace_previous_data()
    {
        $form = $this->runtimeForm(SimpleRequest::class)->submit(['foo' => 'aaa', 'bar' => 'bbb']);
        $submitted = $form->submit(['foo' => 'ccc']);

        $this->assertInstanceOf(SubmittedFormInterface::class, $submitted);
        $this->assertSame(['foo' => 'ccc', 'bar' => 'bbb'], $submitted->httpValue());
        $this->assertEquals('ccc', $submitted->value()->foo);
        $this->assertEquals('bbb', $submitted->value()->bar);
    }

    public function test_submit_should_replace_previous_data_recursively()
    {
        $form = $this->runtimeForm(WithEmbedded::class)->submit([
            'foo' => 'aaa',
            'bar' => 'bbb',
            'embedded' => [
                'baz' => 'ccc',
                'rab' => 'ddd',
            ],
        ]);
        $submitted = $form->submit(['embedded' => ['baz' => 'eee']]);

        $this->assertSame([
            'foo' => 'aaa',
            'bar' => 'bbb',
            'embedded' => [
                'baz' => 'eee',
                'rab' => 'ddd',
            ],
        ], $submitted->httpValue());
        $this->assertEquals('aaa', $submitted->value()->foo);
        $this->assertEquals('bbb', $submitted->value()->bar);
        $this->assertEquals('eee', $submitted->value()->embedded->baz);
        $this->assertEquals('ddd', $submitted->value()->embedded->rab);
    }

    public function test_view()
    {
        $form = $this->runtimeForm(SimpleRequest::class)->submit(['foo' => 'aaa', 'bar' => 'bbb']);

        $view = $form->view();

        $this->assertEquals('<input name="foo" value="aaa" />', (string) $view['foo']);
        $this->assertEquals('<input name="bar" value="bbb" />', (string) $view['bar']);
    }

    public function test_view_with_errors()
    {
        $form = $this->runtimeForm(RequiredParametersRequest::class)->submit([]);

        $view = $form->view();

        $this->assertEquals('<input name="foo" value="" required />', (string) $view['foo']);
        $this->assertEquals('<input name="bar" value="" required minlength="3" />', (string) $view['bar']);

        $this->assertEquals('This value is required', $view['foo']->error);
        $this->assertEquals('bar must be set', $view['bar']->error);
    }

    public function test_view_not_enabled()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('View system disabled for the form');

        $form = (DefaultFormFactory::runtime(enabledView: false))->create(SimpleRequest::class)->submit(['foo' => 'aaa', 'bar' => 'bbb']);

        $form->view();
    }
}
