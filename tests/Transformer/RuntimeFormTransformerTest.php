<?php

namespace Quatrevieux\Form\Transformer;

use Exception;
use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\Csv;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\TransformationError;
use Quatrevieux\Form\Validator\FieldError;

class RuntimeFormTransformerTest extends FormTestCase
{
    public function test_getters()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            $transformers = [
                'foo' => [new Csv(), new Cast(CastType::Array)],
                'bar' => [new Cast(CastType::Int)],
            ],
            $mapping = [
                'foo' => '__foo',
            ],
            $errorConfig = [
                'bar' => new TransformationError(message: 'bar error'),
            ],
        );

        $this->assertEquals($transformers, $transformer->getFieldsTransformers());
        $this->assertEquals($mapping, $transformer->getFieldsNameMapping());
        $this->assertEquals($errorConfig, $transformer->getFieldsTransformationErrors());
    }

    public function test_without_transformers()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [],
            ],
            [],
            []
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], []), $transformer->transformFromHttp(['foo' => 'bar', 'other' => 'ignored']));
        $this->assertEquals(['foo' => 'bar'], $transformer->transformToHttp(['foo' => 'bar', 'other' => 'ignored']));
    }

    public function test_with_transformation_error()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer()],
            ],
            [],
            []
        );

        $this->assertEquals(new TransformationResult(['foo' => null], ['foo' => new FieldError('my transformation error', code: TransformationError::CODE)]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_error_custom_message()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer()],
            ],
            [],
            [
                'foo' => new TransformationError(message: 'my custom error'),
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => null], ['foo' => new FieldError('my custom error', code: TransformationError::CODE)]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_error_custom_code()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer()],
            ],
            [],
            [
                'foo' => new TransformationError(code: 'd2e95635-fdb6-4752-acb4-aa8f76f64de6'),
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => null], ['foo' => new FieldError('my transformation error', code: 'd2e95635-fdb6-4752-acb4-aa8f76f64de6')]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_error_ignored()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer()],
            ],
            [],
            [
                'foo' => new TransformationError(ignore: true),
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => null], []), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_keep_original_value()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer()],
            ],
            [],
            [
                'foo' => new TransformationError(keepOriginalValue: true),
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], ['foo' => new FieldError('my transformation error', code: TransformationError::CODE)]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_error_ignored_and_keep_original_value()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer()],
            ],
            [],
            [
                'foo' => new TransformationError(ignore: true, keepOriginalValue: true),
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], []), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_field_mapping()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [],
            ],
            [
                'foo' => 'bar',
            ],
            []
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], []), $transformer->transformFromHttp(['bar' => 'bar', 'other' => 'ignored']));
        $this->assertEquals(['bar' => 'bar'], $transformer->transformToHttp(['foo' => 'bar', 'other' => 'ignored']));
    }

    public function test_with_transformers_and_mapping()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new Csv(), new Cast(CastType::Array)],
                'bar' => [new Cast(CastType::Int)],
            ],
            [
                'foo' => '__foo',
            ],
            []
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
            [],
            []
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], []), $transformer->transformFromHttp(['foo' => base64_encode('bar')]));
        $this->assertEquals(['foo' => base64_encode('bar')], $transformer->transformToHttp(['foo' => 'bar']));
    }
}

class MyDelegatedTransformer implements DelegatedFieldTransformerInterface
{
    public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface
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

class FailingTransformer implements FieldTransformerInterface
{
    public function transformFromHttp(mixed $value): mixed
    {
        throw new Exception('my transformation error');
    }

    public function transformToHttp(mixed $value): mixed
    {
        return $value;
    }

    public function canThrowError(): bool
    {
        return true;
    }
}
