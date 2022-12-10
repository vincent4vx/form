<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\Cast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\Csv;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Transformer\Field\NullFieldTransformerRegistry;
use Quatrevieux\Form\Transformer\FormTransformerInterface;
use Quatrevieux\Form\Transformer\RuntimeFormTransformer;
use Quatrevieux\Form\Validator\FieldError;

class FormTransformerGeneratorTest extends FormTestCase
{
    public function test_generate_without_transformers()
    {
        $generator = new FormTransformerGenerator();

        $code = $generator->generate('TestingTransformerWithoutFieldTransformers', new RuntimeFormTransformer(
            new NullFieldTransformerRegistry(),
            [
                'foo' => [],
                'bar' => [],
            ],
            []
        ));

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class TestingTransformerWithoutFieldTransformers implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'foo' => $value['foo'] ?? null,
            'bar' => $value['bar'] ?? null,
        ];

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'foo' => $value['foo'] ?? null,
            'bar' => $value['bar'] ?? null,
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , $code);

        $this->assertGeneratedClass($code, 'TestingTransformerWithoutFieldTransformers', FormTransformerInterface::class);
        $transformer = new \TestingTransformerWithoutFieldTransformers(new NullFieldTransformerRegistry());

        $this->assertSame(['foo' => null, 'bar' => null], $transformer->transformFromHttp([])->values);
        $this->assertEmpty($transformer->transformFromHttp([])->errors);
        $this->assertSame(['foo' => 123, 'bar' => 456], $transformer->transformFromHttp(['foo' => 123, 'bar' => 456])->values);
        $this->assertEmpty($transformer->transformFromHttp(['foo' => 123, 'bar' => 456])->errors);

        $this->assertSame(['foo' => null, 'bar' => null], $transformer->transformToHttp([]));
        $this->assertSame(['foo' => 123, 'bar' => 456], $transformer->transformToHttp(['foo' => 123, 'bar' => 456]));
    }

    public function test_generate_with_field_mapping()
    {
        $generator = new FormTransformerGenerator();

        $code = $generator->generate('TestingTransformerWithFieldMapping', new RuntimeFormTransformer(
            new NullFieldTransformerRegistry(),
            [
                'foo' => [],
                'bar' => [],
            ],
            [
                'foo' => 'f_o_o',
                'bar' => 'b_a_r',
            ]
        ));

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class TestingTransformerWithFieldMapping implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'foo' => $value['f_o_o'] ?? null,
            'bar' => $value['b_a_r'] ?? null,
        ];

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'f_o_o' => $value['foo'] ?? null,
            'b_a_r' => $value['bar'] ?? null,
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , $code);

        $this->assertGeneratedClass($code, 'TestingTransformerWithFieldMapping', FormTransformerInterface::class);
        $transformer = new \TestingTransformerWithFieldMapping(new NullFieldTransformerRegistry());

        $this->assertSame(['foo' => 123, 'bar' => 456], $transformer->transformFromHttp(['f_o_o' => 123, 'b_a_r' => 456])->values);
        $this->assertEmpty($transformer->transformFromHttp(['f_o_o' => 123, 'b_a_r' => 456])->errors);
        $this->assertSame(['f_o_o' => 123, 'b_a_r' => 456], $transformer->transformToHttp(['foo' => 123, 'bar' => 456]));
    }

    public function test_generate_with_transformers_and_field_mapping()
    {
        $generator = new FormTransformerGenerator();

        $code = $generator->generate('TestingTransformerWithTransformers', new RuntimeFormTransformer(
            new NullFieldTransformerRegistry(),
            [
                'foo' => [new Csv(), new Cast(CastType::Array)],
                'bar' => [new Cast(CastType::Int)],
            ],
            [
                'foo' => 'f_o_o',
                'bar' => 'b_a_r',
            ]
        ));

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class TestingTransformerWithTransformers implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'foo' => (($__tmp_0f0c348b6cd003c8ae635417270d3a4d = (is_string($__tmp_5c44ecf262daa39f16eef451ec5e7c55 = $value['f_o_o'] ?? null) ? str_getcsv($__tmp_5c44ecf262daa39f16eef451ec5e7c55, ',', '', '') : null)) !== null ? (array) $__tmp_0f0c348b6cd003c8ae635417270d3a4d : null),
            'bar' => (is_scalar($__tmp_f36c42ca1e803ec1f4684adea78ba7dc = $value['b_a_r'] ?? null) ? (int) $__tmp_f36c42ca1e803ec1f4684adea78ba7dc : null),
        ];

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'f_o_o' => (is_array($__tmp_b43d73a92f49a16c5c6761c9e0e4ee72 = (($__tmp_8f4ee22287b10f019cf66bcea64b29b1 = $value['foo'] ?? null) === null || is_scalar($__tmp_8f4ee22287b10f019cf66bcea64b29b1) ? $__tmp_8f4ee22287b10f019cf66bcea64b29b1 : (array) $__tmp_8f4ee22287b10f019cf66bcea64b29b1)) ? implode(',', $__tmp_b43d73a92f49a16c5c6761c9e0e4ee72) : null),
            'b_a_r' => (($__tmp_18be4920f0fd7449d8f97cd9dcd226d5 = $value['bar'] ?? null) === null || is_scalar($__tmp_18be4920f0fd7449d8f97cd9dcd226d5) ? $__tmp_18be4920f0fd7449d8f97cd9dcd226d5 : (array) $__tmp_18be4920f0fd7449d8f97cd9dcd226d5),
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , $code);

        $this->assertGeneratedClass($code, 'TestingTransformerWithTransformers', FormTransformerInterface::class);
        $transformer = new \TestingTransformerWithTransformers(new NullFieldTransformerRegistry());

        $this->assertSame(['foo' => ['12', '3'], 'bar' => 456], $transformer->transformFromHttp(['f_o_o' => '12,3', 'b_a_r' => '456'])->values);
        $this->assertEmpty($transformer->transformFromHttp(['f_o_o' => '12,3', 'b_a_r' => '456'])->errors);
        $this->assertSame(['f_o_o' => '12,3', 'b_a_r' => 456], $transformer->transformToHttp(['foo' => ['12', '3'], 'bar' => 456]));
    }

    public function test_generate_with_delegated_transformer()
    {
        $generator = new FormTransformerGenerator();

        $this->container->set(DelegatedTransformerImpl::class, new DelegatedTransformerImpl());

        $code = $generator->generate('TestingTransformerWithDelegatedTransformer', new RuntimeFormTransformer(
            new ContainerRegistry($this->container),
            ['foo' => [new DelegatedTransformerParameters('z')]],
            []
        ));

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class TestingTransformerWithDelegatedTransformer implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
        ];

        try {
            $transformed['foo'] = ($__transformer_80b8193e49fb60b95b7a65153fe6724c = new \Quatrevieux\Form\Transformer\Generator\DelegatedTransformerParameters(a: 'z'))->getTransformer($this->registry)->transformFromHttp($__transformer_80b8193e49fb60b95b7a65153fe6724c, $value['foo'] ?? null);
        } catch (\Exception $e) {
            $errors['foo'] = new FieldError($e->getMessage());
            $transformed['foo'] = null;
        }

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'foo' => ($__transformer_80b8193e49fb60b95b7a65153fe6724c = new \Quatrevieux\Form\Transformer\Generator\DelegatedTransformerParameters(a: 'z'))->getTransformer($this->registry)->transformToHttp($__transformer_80b8193e49fb60b95b7a65153fe6724c, $value['foo'] ?? null),
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , $code);

        $this->assertGeneratedClass($code, 'TestingTransformerWithDelegatedTransformer', FormTransformerInterface::class);
        $transformer = new \TestingTransformerWithDelegatedTransformer(new ContainerRegistry($this->container));

        $this->assertSame(['foo' => 'zbarz'], $transformer->transformFromHttp(['foo' => 'bar'])->values);
        $this->assertEmpty($transformer->transformFromHttp(['foo' => 'bar'])->errors);
        $this->assertSame(['foo' => 'bar'], $transformer->transformToHttp(['foo' => 'zbarz']));
    }

    public function test_generate_with_generic_transformer_generator()
    {
        $generator = new FormTransformerGenerator();

        $this->container->set(DelegatedTransformerImpl::class, new DelegatedTransformerImpl());

        $code = $generator->generate('TestingTransformerWithGenericTransformerGenerator', new RuntimeFormTransformer(
            new ContainerRegistry($this->container),
            ['foo' => [new WithoutGenerator(5)]],
            []
        ));

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class TestingTransformerWithGenericTransformerGenerator implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'foo' => (new \Quatrevieux\Form\Transformer\Generator\WithoutGenerator(value: 5))->transformFromHttp($value['foo'] ?? null),
        ];

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'foo' => (new \Quatrevieux\Form\Transformer\Generator\WithoutGenerator(value: 5))->transformToHttp($value['foo'] ?? null),
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , $code);

        $this->assertGeneratedClass($code, 'TestingTransformerWithGenericTransformerGenerator', FormTransformerInterface::class);
        $transformer = new \TestingTransformerWithGenericTransformerGenerator(new ContainerRegistry($this->container));

        $this->assertSame(['foo' => 17], $transformer->transformFromHttp(['foo' => 12])->values);
        $this->assertEmpty($transformer->transformFromHttp(['foo' => 12])->errors);
        $this->assertSame(['foo' => 12], $transformer->transformToHttp(['foo' => 17]));
    }

    public function test_generate_with_unsafe_transformer()
    {
        $generator = new FormTransformerGenerator();

        $code = $generator->generate('TestingTransformerWithUnsafeTransformers', new RuntimeFormTransformer(
            new NullFieldTransformerRegistry(),
            [
                'foo' => [new FailingTransformer()],
                'bar' => [],
            ],
            []
        ));

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class TestingTransformerWithUnsafeTransformers implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'bar' => $value['bar'] ?? null,
        ];

        try {
            $transformed['foo'] = (new \Quatrevieux\Form\Transformer\Generator\FailingTransformer())->transformFromHttp($value['foo'] ?? null);
        } catch (\Exception $e) {
            $errors['foo'] = new FieldError($e->getMessage());
            $transformed['foo'] = null;
        }

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'foo' => (new \Quatrevieux\Form\Transformer\Generator\FailingTransformer())->transformToHttp($value['foo'] ?? null),
            'bar' => $value['bar'] ?? null,
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
            , $code);

        $this->assertGeneratedClass($code, 'TestingTransformerWithUnsafeTransformers', FormTransformerInterface::class);
        $transformer = new \TestingTransformerWithUnsafeTransformers(new NullFieldTransformerRegistry());

        $this->assertSame(['bar' => null, 'foo' => null], $transformer->transformFromHttp([])->values);
        $this->assertEquals(['foo' => new FieldError('my error')], $transformer->transformFromHttp([])->errors);
        $this->assertSame(['bar' => 456, 'foo' => null], $transformer->transformFromHttp(['foo' => 123, 'bar' => 456])->values);
        $this->assertEquals(['foo' => new FieldError('my error')], $transformer->transformFromHttp([])->errors);

        $this->assertSame(['foo' => null, 'bar' => null], $transformer->transformToHttp([]));
        $this->assertSame(['foo' => 123, 'bar' => 456], $transformer->transformToHttp(['foo' => 123, 'bar' => 456]));
    }

}

class DelegatedTransformerParameters implements DelegatedFieldTransformerInterface
{
    public function __construct(
        public string $a,
    ) {
    }

    public function getTransformer(FieldTransformerRegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return $registry->getTransformer(DelegatedTransformerImpl::class);
    }
}

class DelegatedTransformerImpl implements ConfigurableFieldTransformerInterface
{
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return $configuration->a . $value . $configuration->a;
    }

    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return trim($value, $configuration->a);
    }
}

class WithoutGenerator implements FieldTransformerInterface
{
    public function __construct(
        private readonly int $value
    ) {
    }

    public function transformFromHttp(mixed $value): mixed
    {
        return $value + $this->value;
    }

    public function transformToHttp(mixed $value): mixed
    {
        return $value - $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        return false;
    }
}

class FailingTransformer implements FieldTransformerInterface
{
    public function transformFromHttp(mixed $value): mixed
    {
        throw new \Exception('my error');
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
