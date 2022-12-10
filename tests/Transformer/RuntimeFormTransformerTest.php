<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\Csv;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Transformer\Field\NullFieldTransformerRegistry;

class RuntimeFormTransformerTest extends FormTestCase
{
    public function test_getters()
    {
        $transformer = new RuntimeFormTransformer(
            new NullFieldTransformerRegistry(),
            $transformers = [
                'foo' => [new Csv(), new Cast(CastType::Array)],
                'bar' => [new Cast(CastType::Int)],
            ],
            $mapping = [
                'foo' => '__foo',
            ]
        );

        $this->assertEquals($transformers, $transformer->getFieldsTransformers());
        $this->assertEquals($mapping, $transformer->getFieldsNameMapping());
    }

    public function test_without_transformers()
    {
        $transformer = new RuntimeFormTransformer(
            new NullFieldTransformerRegistry(),
            [
                'foo' => [],
            ],
            []
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], []), $transformer->transformFromHttp(['foo' => 'bar', 'other' => 'ignored']));
        $this->assertEquals(['foo' => 'bar'], $transformer->transformToHttp(['foo' => 'bar', 'other' => 'ignored']));
    }

    public function test_with_field_mapping()
    {
        $transformer = new RuntimeFormTransformer(
            new NullFieldTransformerRegistry(),
            [
                'foo' => [],
            ],
            [
                'foo' => 'bar',
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], []), $transformer->transformFromHttp(['bar' => 'bar', 'other' => 'ignored']));
        $this->assertEquals(['bar' => 'bar'], $transformer->transformToHttp(['foo' => 'bar', 'other' => 'ignored']));
    }

    public function test_with_transformers_and_mapping()
    {
        $transformer = new RuntimeFormTransformer(
            new NullFieldTransformerRegistry(),
            [
                'foo' => [new Csv(), new Cast(CastType::Array)],
                'bar' => [new Cast(CastType::Int)],
            ],
            [
                'foo' => '__foo',
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => ['bar', 'baz'], 'bar' => 42], []), $transformer->transformFromHttp(['__foo' => 'bar,baz', 'bar' => '42']));
        $this->assertEquals(['__foo' => 'bar,baz', 'bar' => '42'], $transformer->transformToHttp(['foo' => ['bar', 'baz'], 'bar' => 42]));
    }

    public function test_with_delegated_transformer()
    {
        $this->container->set(MyDelegatedTransformerImpl::class, new MyDelegatedTransformerImpl());

        $transformer = new RuntimeFormTransformer(
            new ContainerRegistry($this->container),
            [
                'foo' => [new MyDelegatedTransformer()]
            ],
            []
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], []), $transformer->transformFromHttp(['foo' => base64_encode('bar')]));
        $this->assertEquals(['foo' => base64_encode('bar')], $transformer->transformToHttp(['foo' => 'bar']));
    }
}

class MyDelegatedTransformer implements DelegatedFieldTransformerInterface
{
    public function getTransformer(FieldTransformerRegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return $registry->getTransformer(MyDelegatedTransformerImpl::class);
    }
}

class MyDelegatedTransformerImpl implements ConfigurableFieldTransformerInterface
{
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return base64_decode($value);
    }

    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return base64_encode($value);
    }
}