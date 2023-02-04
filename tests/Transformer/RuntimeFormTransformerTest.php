<?php

namespace Quatrevieux\Form\Transformer;

use Exception;
use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\DummyTranslator;
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

        $this->assertEquals($transformers, $transformer->fieldsTransformers);
        $this->assertEquals($mapping, $transformer->fieldsNameMapping);
        $this->assertEquals($errorConfig, $transformer->fieldsTransformationErrors);
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

        $this->assertEquals(new TransformationResult(['foo' => null], ['foo' => new FieldError('my transformation error', code: TransformationError::CODE, translator: DummyTranslator::instance())]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_exception()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer(true)],
            ],
            [],
            []
        );

        $this->assertEquals(new TransformationResult(['foo' => null], ['foo' => ['foo' => new FieldError('my sub error', code: '67f20e07-9dc2-4aa0-8521-f4fb08ad23ad')]]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_exception_hidden()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer(true)],
            ],
            [],
            [
                'foo' => new TransformationError(hideSubErrors: true),
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => null], ['foo' => new FieldError('my sub error', code: TransformationError::CODE, translator: DummyTranslator::instance())]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_error_translated()
    {
        $this->configureTranslator('fr', [
            'my transformation error' => 'mon erreur de transformation',
        ]);

        $transformer = new RuntimeFormTransformer(
            new ContainerRegistry($this->container),
            [
                'foo' => [new FailingTransformer()],
            ],
            [],
            []
        );

        $this->assertErrors(['foo' => 'mon erreur de transformation'], $transformer->transformFromHttp(['foo' => 'bar'])->errors);
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

        $this->assertEquals(new TransformationResult(['foo' => null], ['foo' => new FieldError('my custom error', code: TransformationError::CODE, translator: DummyTranslator::instance())]), $transformer->transformFromHttp(['foo' => 'bar']));
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

        $this->assertEquals(new TransformationResult(['foo' => null], ['foo' => new FieldError('my transformation error', code: 'd2e95635-fdb6-4752-acb4-aa8f76f64de6', translator: DummyTranslator::instance())]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_with_transformation_error_ignored(bool $subErrors)
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer($subErrors)],
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

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], ['foo' => new FieldError('my transformation error', code: TransformationError::CODE, translator: DummyTranslator::instance())]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    public function test_with_transformation_exception_keep_original_value()
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer(true)],
            ],
            [],
            [
                'foo' => new TransformationError(keepOriginalValue: true),
            ]
        );

        $this->assertEquals(new TransformationResult(['foo' => 'bar'], ['foo' => ['foo' => new FieldError('my sub error', code: '67f20e07-9dc2-4aa0-8521-f4fb08ad23ad')]]), $transformer->transformFromHttp(['foo' => 'bar']));
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_with_transformation_error_ignored_and_keep_original_value(bool $subErrors)
    {
        $transformer = new RuntimeFormTransformer(
            new DefaultRegistry(),
            [
                'foo' => [new FailingTransformer($subErrors)],
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
        return $registry->getFieldTransformer(MyDelegatedTransformerImpl::class);
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
    public function __construct(public readonly bool $subError = false)
    {
    }

    public function transformFromHttp(mixed $value): mixed
    {
        $this->subError
            ? throw new TransformerException('my sub error', ['foo' => new FieldError('my sub error', code: '67f20e07-9dc2-4aa0-8521-f4fb08ad23ad')])
            : throw new Exception('my transformation error')
        ;
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
