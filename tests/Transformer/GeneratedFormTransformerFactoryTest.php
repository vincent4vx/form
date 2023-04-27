<?php

namespace Quatrevieux\Form\Transformer;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\WithTransformerRequest;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Util\Functions;

class GeneratedFormTransformerFactoryTest extends FormTestCase
{
    public function test_create_simple()
    {
        $factory = new GeneratedFormTransformerFactory(
            registry: new ContainerRegistry($this->container),
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            classNameResolver: fn (string $className) => str_replace('\\', '_', $className) . 'TransformerGeneratorTesting',
        );

        $this->assertInstanceOf(FormTransformerInterface::class, $factory->create(SimpleRequest::class));
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestTransformerGeneratorTesting', $factory->create(SimpleRequest::class));

        $this->assertEquals(<<<'PHP'
<?php

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class Quatrevieux_Form_Fixtures_SimpleRequestTransformerGeneratorTesting extends Quatrevieux\Form\Transformer\AbstractGeneratedFormTransformer
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'foo' => (is_scalar($__tmp_8f4ee22287b10f019cf66bcea64b29b1 = $value['foo'] ?? null) || $__tmp_8f4ee22287b10f019cf66bcea64b29b1 instanceof \Stringable ? (string) $__tmp_8f4ee22287b10f019cf66bcea64b29b1 : null),
            'bar' => (is_scalar($__tmp_18be4920f0fd7449d8f97cd9dcd226d5 = $value['bar'] ?? null) || $__tmp_18be4920f0fd7449d8f97cd9dcd226d5 instanceof \Stringable ? (string) $__tmp_18be4920f0fd7449d8f97cd9dcd226d5 : null),
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

    public function transformFieldFromHttp(string $fieldName, mixed $value): mixed
    {
        return match ($fieldName) {
            'foo' => (is_scalar($__tmp_7d0596c36891967f3bb9d994b4a97c19 = $value) || $__tmp_7d0596c36891967f3bb9d994b4a97c19 instanceof \Stringable ? (string) $__tmp_7d0596c36891967f3bb9d994b4a97c19 : null),
            'bar' => (is_scalar($__tmp_7d0596c36891967f3bb9d994b4a97c19 = $value) || $__tmp_7d0596c36891967f3bb9d994b4a97c19 instanceof \Stringable ? (string) $__tmp_7d0596c36891967f3bb9d994b4a97c19 : null),
        };
    }

    public function transformFieldToHttp(string $fieldName, mixed $value): mixed
    {
        return match ($fieldName) {
            'foo' => $value,
            'bar' => $value,
        };
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_SimpleRequestTransformerGeneratorTesting.php'));

        $transformer = $factory->create(SimpleRequest::class);

        $this->assertSame([
            'foo' => 'foo',
            'bar' => 'bar',
        ], $transformer->transformFromHttp([
            'foo' => 'foo',
            'bar' => 'bar',
        ])->values);
        $this->assertEmpty($transformer->transformFromHttp([
            'foo' => 'foo',
            'bar' => 'bar',
        ])->errors);

        $this->assertSame([
            'foo' => 'foo',
            'bar' => 'bar',
        ], $transformer->transformToHttp([
            'foo' => 'foo',
            'bar' => 'bar',
        ]));

        $this->assertSame('foo', $transformer->fieldTransformer('foo')->transformFromHttp('foo'));
        $this->assertSame('foo', $transformer->fieldTransformer('foo')->transformToHttp('foo'));
        $this->assertTrue($transformer->fieldTransformer('foo')->canThrowError());
        $this->assertSame('foo', $transformer->fieldTransformer('bar')->transformFromHttp('foo'));
        $this->assertSame('foo', $transformer->fieldTransformer('bar')->transformToHttp('foo'));
        $this->assertTrue($transformer->fieldTransformer('bar')->canThrowError());
    }

    public function test_create_with_transformers()
    {
        $factory = new GeneratedFormTransformerFactory(
            registry: new ContainerRegistry($this->container),
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            classNameResolver: fn (string $className) => str_replace('\\', '_', $className) . 'TransformerGeneratorTesting',
        );

        $this->assertInstanceOf(FormTransformerInterface::class, $factory->create(WithTransformerRequest::class));
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_WithTransformerRequestTransformerGeneratorTesting', $factory->create(WithTransformerRequest::class));

        $this->assertEquals(<<<'PHP'
<?php

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class Quatrevieux_Form_Fixtures_WithTransformerRequestTransformerGeneratorTesting extends Quatrevieux\Form\Transformer\AbstractGeneratedFormTransformer
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'list' => (($__tmp_d2f1517cbdc1e8f0e3107ec10dc0e518 = (is_string($__tmp_ccc11a38b775e3f7281e431235032257 = $value['list'] ?? null) ? str_getcsv($__tmp_ccc11a38b775e3f7281e431235032257, ',', '"', '') : null)) !== null ? (array) $__tmp_d2f1517cbdc1e8f0e3107ec10dc0e518 : null),
        ];

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'list' => (is_array($__tmp_ccc11a38b775e3f7281e431235032257 = $value['list'] ?? null) ? \Quatrevieux\Form\Transformer\Field\Csv::toCsv($__tmp_ccc11a38b775e3f7281e431235032257, ',', '"') : null),
        ];
    }

    public function transformFieldFromHttp(string $fieldName, mixed $value): mixed
    {
        return match ($fieldName) {
            'list' => (($__tmp_34760304f8f15325b0a5dfbc9eb93d1a = (is_string($__tmp_7d0596c36891967f3bb9d994b4a97c19 = $value) ? str_getcsv($__tmp_7d0596c36891967f3bb9d994b4a97c19, ',', '"', '') : null)) !== null ? (array) $__tmp_34760304f8f15325b0a5dfbc9eb93d1a : null),
        };
    }

    public function transformFieldToHttp(string $fieldName, mixed $value): mixed
    {
        return match ($fieldName) {
            'list' => (is_array($__tmp_7d0596c36891967f3bb9d994b4a97c19 = $value) ? \Quatrevieux\Form\Transformer\Field\Csv::toCsv($__tmp_7d0596c36891967f3bb9d994b4a97c19, ',', '"') : null),
        };
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_WithTransformerRequestTransformerGeneratorTesting.php'));

        $transformer = $factory->create(WithTransformerRequest::class);

        $this->assertSame([
            'list' => ['foo', 'bar'],
        ], $transformer->transformFromHttp([
            'list' => 'foo,bar',
        ])->values);
        $this->assertEmpty($transformer->transformFromHttp([
            'list' => 'foo,bar',
        ])->errors);

        $this->assertSame([
            'list' => 'foo,bar',
        ], $transformer->transformToHttp([
            'list' => ['foo', 'bar'],
        ]));

        $this->assertSame(['foo', 'bar'], $transformer->fieldTransformer('list')->transformFromHttp('foo,bar'));
        $this->assertSame('foo,bar', $transformer->fieldTransformer('list')->transformToHttp(['foo', 'bar']));
    }
}
