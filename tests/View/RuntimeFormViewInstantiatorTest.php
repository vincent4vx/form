<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\DummyTranslator;
use Quatrevieux\Form\Embedded\Embedded;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\Constraint\Choice;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\Provider\FieldViewConfiguration;

class RuntimeFormViewInstantiatorTest extends FormTestCase
{
    public function test_empty()
    {
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [], [], [], []);

        $view = $instantiator->default();

        $this->assertEquals([], $view->fields);
        $this->assertEquals([], $view->value);

        $view = $instantiator->submitted(['foo' => 'bar'], []);

        $this->assertEquals([], $view->fields);
        $this->assertEquals(['foo' => 'bar'], $view->value);
    }

    public function test_with_fields()
    {
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [
            'foo' => new FieldViewConfiguration(),
            'bar' => new FieldViewConfiguration(),
        ], [], [], []);

        $view = $instantiator->default();

        $this->assertEquals([
            'foo' => new FieldView('foo', null, null, []),
            'bar' => new FieldView('bar', null, null, []),
        ], $view->fields);
        $this->assertEquals([], $view->value);

        $view = $instantiator->submitted(['foo' => 'bar'], []);

        $this->assertEquals([
            'foo' => new FieldView('foo', 'bar', null, []),
            'bar' => new FieldView('bar', null, null, []),
        ], $view->fields);
        $this->assertEquals(['foo' => 'bar'], $view->value);

        $view = $instantiator->submitted(['foo' => 'aaa', 'bar' => 'bbb'], ['foo' => new FieldError('my error')]);

        $this->assertEquals([
            'foo' => new FieldView('foo', 'aaa', new FieldError('my error'), []),
            'bar' => new FieldView('bar', 'bbb', null, []),
        ], $view->fields);
        $this->assertEquals(['foo' => 'aaa', 'bar' => 'bbb'], $view->value);
    }

    public function test_with_fields_and_root()
    {
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [
            'foo' => new FieldViewConfiguration(),
            'bar' => new FieldViewConfiguration(),
        ], [], [], []);

        $view = $instantiator->default('root[foo]');

        $this->assertEquals([
            'foo' => new FieldView('root[foo][foo]', null, null, []),
            'bar' => new FieldView('root[foo][bar]', null, null, []),
        ], $view->fields);
    }

    public function test_with_fields_mapping()
    {
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [
            'foo' => new FieldViewConfiguration(),
            'bar' => new FieldViewConfiguration(),
        ], [
            'foo' => 'my_foo',
            'bar' => 'my_bar',
        ], [], []);

        $view = $instantiator->default();

        $this->assertEquals([
            'foo' => new FieldView('my_foo', null, null, []),
            'bar' => new FieldView('my_bar', null, null, []),
        ], $view->fields);
        $this->assertEquals([], $view->value);

        $view = $instantiator->submitted(['my_foo' => 'bar'], []);

        $this->assertEquals([
            'foo' => new FieldView('my_foo', 'bar', null, []),
            'bar' => new FieldView('my_bar', null, null, []),
        ], $view->fields);
        $this->assertEquals(['my_foo' => 'bar'], $view->value);

        $view = $instantiator->submitted(['my_foo' => 'aaa', 'my_bar' => 'bbb'], ['foo' => new FieldError('my error')]);

        $this->assertEquals([
            'foo' => new FieldView('my_foo', 'aaa', new FieldError('my error'), []),
            'bar' => new FieldView('my_bar', 'bbb', null, []),
        ], $view->fields);
        $this->assertEquals(['my_foo' => 'aaa', 'my_bar' => 'bbb'], $view->value);
    }

    public function test_with_embedded_and_custom_config()
    {
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [
            'foo' => new FieldViewConfiguration(id: 'my_foo', defaultValue: 'aaa'),
            'bar' => new FieldViewConfiguration(attributes: ['class' => 'my_class']),
            'embedded' => new Embedded(SimpleRequest::class),
        ], [], [], []);

        $view = $instantiator->default();

        $this->assertEquals([
            'foo' => new FieldView('foo', 'aaa', null, ['id' => 'my_foo']),
            'bar' => new FieldView('bar', null, null, ['class' => 'my_class']),
            'embedded' => new FormView([
                'foo' => new FieldView('embedded[foo]', null, null, []),
                'bar' => new FieldView('embedded[bar]', null, null, []),
            ], []),
        ], $view->fields);
        $this->assertEquals([], $view->value);

        $view = $instantiator->submitted([
            'foo' => 'bar',
            'embedded' => [
                'foo' => 'aaa',
                'bar' => 'bbb',
            ],
        ], [
            'foo' => new FieldError('my error'),
            'embedded' => [
                'bar' => new FieldError('other error'),
            ],
        ]);

        $this->assertEquals([
            'foo' => new FieldView('foo', 'bar', new FieldError('my error'), ['id' => 'my_foo']),
            'bar' => new FieldView('bar', null, null, ['class' => 'my_class']),
            'embedded' => new FormView([
                'foo' => new FieldView('embedded[foo]', 'aaa', null, []),
                'bar' => new FieldView('embedded[bar]', 'bbb', new FieldError('other error'), []),
            ], [
                'foo' => 'aaa',
                'bar' => 'bbb',
            ]),
        ], $view->fields);
    }

    public function test_with_attributes()
    {
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [
            'foo' => new FieldViewConfiguration(),
            'bar' => new FieldViewConfiguration(),
        ], [], ['foo' => ['class' => 'my_class']], []);

        $view = $instantiator->default();

        $this->assertEquals([
            'foo' => new FieldView('foo', null, null, ['class' => 'my_class']),
            'bar' => new FieldView('bar', null, null, []),
        ], $view->fields);
        $this->assertEquals([], $view->value);

        $view = $instantiator->submitted(['foo' => 'bar'], []);

        $this->assertEquals([
            'foo' => new FieldView('foo', 'bar', null, ['class' => 'my_class']),
            'bar' => new FieldView('bar', null, null, []),
        ], $view->fields);
        $this->assertEquals(['foo' => 'bar'], $view->value);

        $view = $instantiator->submitted(['foo' => 'aaa', 'bar' => 'bbb'], ['foo' => new FieldError('my error')]);

        $this->assertEquals([
            'foo' => new FieldView('foo', 'aaa', new FieldError('my error'), ['class' => 'my_class']),
            'bar' => new FieldView('bar', 'bbb', null, []),
        ], $view->fields);
        $this->assertEquals(['foo' => 'aaa', 'bar' => 'bbb'], $view->value);
    }

    public function test_with_choices()
    {
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [
            'foo' => new FieldViewConfiguration(),
        ], [], [], [
            'foo' => new Choice([
                'aaa' => 1,
                'bbb' => 2,
                'ccc' => 3,
            ]),
        ]);

        $view = $instantiator->default();

        $expectedChoices = [
            new ChoiceView(1, 'aaa', false),
            new ChoiceView(2, 'bbb', false),
            new ChoiceView(3, 'ccc', false),
        ];

        $expectedChoices[0]->setTranslator(DummyTranslator::instance());
        $expectedChoices[1]->setTranslator(DummyTranslator::instance());
        $expectedChoices[2]->setTranslator(DummyTranslator::instance());

        $this->assertEquals([
            'foo' => new FieldView('foo', null, null, choices: $expectedChoices),
        ], $view->fields);

        $view = $instantiator->submitted(['foo' => 2], []);

        $expectedChoices = [
            new ChoiceView(1, 'aaa', false),
            new ChoiceView(2, 'bbb', true),
            new ChoiceView(3, 'ccc', false),
        ];

        $expectedChoices[0]->setTranslator(DummyTranslator::instance());
        $expectedChoices[1]->setTranslator(DummyTranslator::instance());
        $expectedChoices[2]->setTranslator(DummyTranslator::instance());

        $this->assertEquals([
            'foo' => new FieldView('foo', 2, null, choices: $expectedChoices),
        ], $view->fields);
    }
}
