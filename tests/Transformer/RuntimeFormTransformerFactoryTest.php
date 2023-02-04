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

        $this->assertEquals([], $transformer->fieldsNameMapping);
        $this->assertEquals([], $transformer->fieldsTransformationErrors);

        $this->assertEquals([
            'foo' => [new Cast(CastType::String)],
            'bar' => [new Cast(CastType::String)],
        ], $transformer->fieldsTransformers);
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

        $this->assertEquals([], $transformer->fieldsNameMapping);
        $this->assertEquals([], $transformer->fieldsTransformationErrors);
        $this->assertEquals([
            'list' => [new Csv(enclosure: '"'), new Cast(CastType::Array)],
        ], $transformer->fieldsTransformers);
    }

    public function test_create_with_transformation_error_configuration()
    {
        $factory = new RuntimeFormTransformerFactory(new ContainerRegistry($this->container));

        $transformer = $factory->create(FailingTransformerRequest::class);

        $this->assertEquals([], $transformer->fieldsNameMapping);
        $this->assertEquals([
            'foo' => [new UnsafeJsonTransformer(), new Cast(CastType::Object)],
            'customTransformerErrorHandling' => [new UnsafeBase64(), new Cast(CastType::String)],
            'ignoreError' => [new UnsafeBase64(), new Cast(CastType::String)],
        ], $transformer->fieldsTransformers);
        $this->assertEquals([
            'customTransformerErrorHandling' => new TransformationError(message: 'invalid data', keepOriginalValue: true, code: 'd2e95635-fdb6-4752-acb4-aa8f76f64de6'),
            'ignoreError' => new TransformationError(ignore: true),
        ], $transformer->fieldsTransformationErrors);
    }

    public function test_create_with_field_name_mapping()
    {
        $factory = new RuntimeFormTransformerFactory(new ContainerRegistry($this->container));

        $transformer = $factory->create(WithFieldNameMapping::class);

        $this->assertEquals([
            'myComplexName' => 'my_complex_name',
            'otherField' => 'other',
        ], $transformer->fieldsNameMapping);
    }
}
