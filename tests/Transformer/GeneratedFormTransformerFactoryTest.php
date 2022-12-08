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

class Quatrevieux_Form_Fixtures_SimpleRequestTransformerGeneratorTesting implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    /**
     * Transform raw HTTP value to array of data object properties values
     *
     * @param mixed[] $value Raw HTTP value
     *
     * @return mixed[] PHP properties values
     */
    function transformFromHttp(array $value): array
    {
        return [
            'foo' => (is_scalar($__tmp_8f4ee22287b10f019cf66bcea64b29b1 = $value['foo'] ?? null) || $__tmp_8f4ee22287b10f019cf66bcea64b29b1 instanceof \Stringable ? (string) $__tmp_8f4ee22287b10f019cf66bcea64b29b1 : null),
            'bar' => (is_scalar($__tmp_18be4920f0fd7449d8f97cd9dcd226d5 = $value['bar'] ?? null) || $__tmp_18be4920f0fd7449d8f97cd9dcd226d5 instanceof \Stringable ? (string) $__tmp_18be4920f0fd7449d8f97cd9dcd226d5 : null),
        ];
    }

    /**
     * Transform data object properties values to normalized HTTP fields
     *
     * @param mixed[] $value Array of properties values
     *
     * @return mixed[] Normalized HTTP fields value
     */
    function transformToHttp(array $value): array
    {
        return [
            'foo' => (($__tmp_8f4ee22287b10f019cf66bcea64b29b1 = $value['foo'] ?? null) === null || is_scalar($__tmp_8f4ee22287b10f019cf66bcea64b29b1) ? $__tmp_8f4ee22287b10f019cf66bcea64b29b1 : (array) $__tmp_8f4ee22287b10f019cf66bcea64b29b1),
            'bar' => (($__tmp_18be4920f0fd7449d8f97cd9dcd226d5 = $value['bar'] ?? null) === null || is_scalar($__tmp_18be4920f0fd7449d8f97cd9dcd226d5) ? $__tmp_18be4920f0fd7449d8f97cd9dcd226d5 : (array) $__tmp_18be4920f0fd7449d8f97cd9dcd226d5),
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
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
        ]));

        $this->assertSame([
            'foo' => 'foo',
            'bar' => 'bar',
        ], $transformer->transformToHttp([
            'foo' => 'foo',
            'bar' => 'bar',
        ]));
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

class Quatrevieux_Form_Fixtures_WithTransformerRequestTransformerGeneratorTesting implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    /**
     * Transform raw HTTP value to array of data object properties values
     *
     * @param mixed[] $value Raw HTTP value
     *
     * @return mixed[] PHP properties values
     */
    function transformFromHttp(array $value): array
    {
        return [
            'list' => (($__tmp_d2f1517cbdc1e8f0e3107ec10dc0e518 = (is_string($__tmp_ccc11a38b775e3f7281e431235032257 = $value['list'] ?? null) ? str_getcsv($__tmp_ccc11a38b775e3f7281e431235032257, ',', '"', '') : null)) !== null ? (array) $__tmp_d2f1517cbdc1e8f0e3107ec10dc0e518 : null),
        ];
    }

    /**
     * Transform data object properties values to normalized HTTP fields
     *
     * @param mixed[] $value Array of properties values
     *
     * @return mixed[] Normalized HTTP fields value
     */
    function transformToHttp(array $value): array
    {
        return [
            'list' => (is_array($__tmp_25470d413f79004c3b8f72ddfdad0ef3 = (($__tmp_ccc11a38b775e3f7281e431235032257 = $value['list'] ?? null) === null || is_scalar($__tmp_ccc11a38b775e3f7281e431235032257) ? $__tmp_ccc11a38b775e3f7281e431235032257 : (array) $__tmp_ccc11a38b775e3f7281e431235032257)) ? \Quatrevieux\Form\Transformer\Field\Csv::toCsv($__tmp_25470d413f79004c3b8f72ddfdad0ef3, ',', '"') : null),
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_WithTransformerRequestTransformerGeneratorTesting.php'));

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
    }
}
