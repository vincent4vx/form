<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\Embedded\Embedded;
use Quatrevieux\Form\Fixtures\EmbeddedForm;
use Quatrevieux\Form\Fixtures\FormWithCustomView;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\WithEmbedded;
use Quatrevieux\Form\Fixtures\WithFieldNameMapping;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\View\Provider\FieldViewConfiguration;

class RuntimeFormViewInstantiatorFactoryTest extends FormTestCase
{
    public function test_create_simple()
    {
        $factory = new RuntimeFormViewInstantiatorFactory($this->registry);

        $instantiator = $factory->create(SimpleRequest::class);

        $this->assertEquals(new RuntimeFormViewInstantiator(
            $this->registry,
            [
                'foo' => new FieldViewConfiguration(),
                'bar' => new FieldViewConfiguration(),
            ],
            [],
            [],
        ), $instantiator);
    }

    public function test_create_with_http_field_mapping()
    {
        $factory = new RuntimeFormViewInstantiatorFactory($this->registry);

        $instantiator = $factory->create(WithFieldNameMapping::class);

        $this->assertEquals(new RuntimeFormViewInstantiator(
            $this->registry,
            [
                'myComplexName' => new FieldViewConfiguration(),
                'otherField' => new FieldViewConfiguration(),
            ],
            [
                'myComplexName' => 'my_complex_name',
                'otherField' => 'other',
            ],
            [],
        ), $instantiator);
    }

    public function test_create_with_custom_view_config()
    {
        $factory = new RuntimeFormViewInstantiatorFactory($this->registry);

        $instantiator = $factory->create(FormWithCustomView::class);

        $this->assertEquals(new RuntimeFormViewInstantiator(
            $this->registry,
            [
                'count' => new FieldViewConfiguration(type: 'number', id: 'form_count', attributes: ['min' => 0, 'max' => 100]),
                'name' => new FieldViewConfiguration(type: 'text', id: 'form_name', defaultValue: 'example'),
            ],
            [],
            [],
        ), $instantiator);
    }

    public function test_create_with_embedded()
    {
        $factory = new RuntimeFormViewInstantiatorFactory($this->registry);

        $instantiator = $factory->create(WithEmbedded::class);

        $this->assertEquals(new RuntimeFormViewInstantiator(
            $this->registry,
            [
                'foo' => new FieldViewConfiguration(),
                'bar' => new FieldViewConfiguration(),
                'embedded' => new Embedded(EmbeddedForm::class),
            ],
            [],
            [],
        ), $instantiator);
    }

    public function test_create_with_required()
    {
        $factory = new RuntimeFormViewInstantiatorFactory($this->registry);

        $instantiator = $factory->create(RequiredParametersRequest::class);

        $this->assertEquals(new RuntimeFormViewInstantiator(
            $this->registry,
            [
                'foo' => new FieldViewConfiguration(),
                'bar' => new FieldViewConfiguration(),
            ],
            [],
            [
                'foo' => ['required' => true],
                'bar' => ['required' => true, 'minlength' => 3],
            ],
        ), $instantiator);
    }
}
