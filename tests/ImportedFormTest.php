<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use Quatrevieux\Form\Fixtures\EmbeddedForm;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\WithEmbedded;

class ImportedFormTest extends FormTestCase
{
    public function test_getters()
    {
        $data = new SimpleRequest();
        $data->foo = 'aaa';
        $data->bar = 'bbb';

        $form = $this->runtimeForm(SimpleRequest::class)->import($data);

        $this->assertSame($data, $form->value());
        $this->assertSame(['foo' => 'aaa', 'bar' => 'bbb'], $form->httpValue());
    }

    public function test_import_should_only_forward_to_base_form()
    {
        $data = new SimpleRequest();
        $data->foo = 'aaa';
        $data->bar = 'bbb';

        $other = new SimpleRequest();
        $other->foo = 'ccc';
        $other->bar = 'ddd';

        $form = $this->runtimeForm(SimpleRequest::class)->import($data);
        $otherForm = $form->import($other);

        $this->assertNotEquals($form, $otherForm);
        $this->assertSame($other, $otherForm->value());
        $this->assertSame(['foo' => 'ccc', 'bar' => 'ddd'], $otherForm->httpValue());
    }

    public function test_submit_should_replace_previous_data()
    {
        $data = new SimpleRequest();
        $data->foo = 'aaa';
        $data->bar = 'bbb';

        $form = $this->runtimeForm(SimpleRequest::class)->import($data);
        $submitted = $form->submit(['foo' => 'ccc']);

        $this->assertInstanceOf(SubmittedFormInterface::class, $submitted);
        $this->assertSame(['foo' => 'ccc', 'bar' => 'bbb'], $submitted->httpValue());
        $this->assertNotEquals($data, $submitted->value());
        $this->assertEquals('ccc', $submitted->value()->foo);
        $this->assertEquals('bbb', $submitted->value()->bar);
    }

    public function test_submit_should_replace_previous_data_recursively()
    {
        $data = new WithEmbedded();
        $data->foo = 'aaa';
        $data->bar = 'bbb';
        $data->embedded = new EmbeddedForm();
        $data->embedded->baz = 'ccc';
        $data->embedded->rab = 'ddd';

        $form = $this->runtimeForm(WithEmbedded::class)->import($data);
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
        $data = new SimpleRequest();
        $data->foo = 'aaa';
        $data->bar = 'bbb';

        $form = $this->runtimeForm(SimpleRequest::class)->import($data);

        $view = $form->view();

        $this->assertEquals('<input name="foo" value="aaa" />', (string) $view['foo']);
        $this->assertEquals('<input name="bar" value="bbb" />', (string) $view['bar']);
    }

    public function test_view_not_enabled()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('View system disabled for the form');

        $data = new SimpleRequest();
        $data->foo = 'aaa';
        $data->bar = 'bbb';

        $form = (DefaultFormFactory::runtime(enabledView: false))->create(SimpleRequest::class)->import($data);

        $form->view();
    }
}
