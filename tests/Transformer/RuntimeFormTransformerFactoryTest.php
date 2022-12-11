<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\Fixtures\FailingTransformerRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\UnsafeBase64;
use Quatrevieux\Form\Fixtures\UnsafeJsonTransformer;
use Quatrevieux\Form\Fixtures\WithFieldNameMapping;
use Quatrevieux\Form\Fixtures\WithTransformerRequest;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\Csv;
use Quatrevieux\Form\Transformer\Field\TransformationError;

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
        ])->values);
        $this->assertSame([
            'foo' => 'foo',
            'bar' => 'bar',
        ], $transformer->transformToHttp([
            'foo' => 'foo',
            'bar' => 'bar',
        ]));

        $this->assertEquals([], $transformer->getFieldsNameMapping());
        $this->assertEquals([], $transformer->getFieldsTransformationErrors());

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
        ])->values);

        $this->assertSame([
            'list' => 'foo,bar',
        ], $transformer->transformToHttp([
            'list' => ['foo', 'bar'],
        ]));

        $this->assertEquals([], $transformer->getFieldsNameMapping());
        $this->assertEquals([], $transformer->getFieldsTransformationErrors());
        $this->assertEquals([
            'list' => [new Csv(enclosure: '"'), new Cast(CastType::Array)],
        ], $transformer->getFieldsTransformers());
    }

    public function test_create_with_transformation_error_configuration()
    {
        $factory = new RuntimeFormTransformerFactory(new ContainerRegistry($this->container));

        $transformer = $factory->create(FailingTransformerRequest::class);

        $this->assertEquals([], $transformer->getFieldsNameMapping());
        $this->assertEquals([
            'foo' => [new UnsafeJsonTransformer(), new Cast(CastType::Object)],
            'customTransformerErrorHandling' => [new UnsafeBase64(), new Cast(CastType::String)],
            'ignoreError' => [new UnsafeBase64(), new Cast(CastType::String)],
        ], $transformer->getFieldsTransformers());
        $this->assertEquals([
            'customTransformerErrorHandling' => new TransformationError(message: 'invalid data', keepOriginalValue: true),
            'ignoreError' => new TransformationError(ignore: true),
        ], $transformer->getFieldsTransformationErrors());
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
