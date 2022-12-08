<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\WithFieldNameMapping;
use Quatrevieux\Form\Fixtures\WithTransformerRequest;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\Csv;

class RuntimeFormTransformerFactoryTest extends FormTestCase
{
    public function test_create_simple()
    {
        $factory = new RuntimeFormTransformerFactory(new ContainerRegistry($this->container));

        $this->assertInstanceOf(RuntimeFormTransformer::class, $factory->create(SimpleRequest::class));

        $transformer = $factory->create(SimpleRequest::class);
        $this->assertSame([
            'foo' => 'foo',
            'bar' => 'bar',
        ], $transformer->transformFromHttp([
            'foo' => 'foo',
            'bar' => 'bar',
        ]));
        $this->assertSame([
            'foo' => 'foo',
            'bar' => 'bar',
        ], $transformer->transformToHttp([
            'foo' => 'foo',
            'bar' => 'bar',
        ]));

        $this->assertEquals([], $transformer->getFieldsNameMapping());

        $this->assertEquals([
            'foo' => [new Cast(CastType::String)],
            'bar' => [new Cast(CastType::String)],
        ], $transformer->getFieldsTransformers());
    }

    public function test_create_with_transformers()
    {
        $factory = new RuntimeFormTransformerFactory(new ContainerRegistry($this->container));

        $this->assertInstanceOf(RuntimeFormTransformer::class, $factory->create(WithTransformerRequest::class));

        $transformer = $factory->create(WithTransformerRequest::class);

        $this->assertSame([
            'list' => ['foo', 'bar'],
        ], $transformer->transformFromHttp([
            'list' => 'foo,bar',
        ]));

        $this->assertSame([
            'list' => 'foo,bar',
        ], $transformer->transformToHttp([
            'list' => ['foo', 'bar'],
        ]));

        $this->assertEquals([], $transformer->getFieldsNameMapping());
        $this->assertEquals([
            'list' => [new Csv(enclosure: '"'), new Cast(CastType::Array)],
        ], $transformer->getFieldsTransformers());
    }

    public function test_create_with_field_name_mapping()
    {
        $factory = new RuntimeFormTransformerFactory(new ContainerRegistry($this->container));

        $transformer = $factory->create(WithFieldNameMapping::class);

        $this->assertEquals([
            'myComplexName' => 'my_complex_name',
            'otherField' => 'other',
        ], $transformer->getFieldsNameMapping());
    }
}
