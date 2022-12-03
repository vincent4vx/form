<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\FormTestCase;

class FormTransformerClassTest extends FormTestCase
{
    public function test_empty()
    {
        $class = new FormTransformerClass('ClassName');
        $class->generateToHttp();
        $class->generateFromHttp();

        $this->assertSame(<<<'PHP'
<?php

class ClassName implements Quatrevieux\Form\Transformer\FormTransformerInterface
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
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , $class->code());
    }

    public function test_declareField()
    {
        $class = new FormTransformerClass('ClassName');

        $class->declareField('foo', 'bar');
        $class->declareField('baz', 'rab');

        $class->generateToHttp();
        $class->generateFromHttp();

        $this->assertSame(<<<'PHP'
<?php

class ClassName implements Quatrevieux\Form\Transformer\FormTransformerInterface
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
            'foo' => $value['bar'] ?? null,
            'baz' => $value['rab'] ?? null,
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
            'bar' => $value['foo'] ?? null,
            'rab' => $value['baz'] ?? null,
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , $class->code());
    }

    public function test_addFieldTransformationExpression()
    {
        $class = new FormTransformerClass('ClassName');

        $class->addFieldTransformationExpression('foo', fn ($v) => "(string) ($v)", fn ($v) => "$v");
        $class->addFieldTransformationExpression('foo', fn ($v) => "base64_decode($v)", fn ($v) => "base64_encode($v)");

        $class->generateToHttp();
        $class->generateFromHttp();

        $this->assertSame(<<<'PHP'
<?php

class ClassName implements Quatrevieux\Form\Transformer\FormTransformerInterface
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
            'foo' => base64_decode((string) ($value['foo'] ?? null)),
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
            'foo' => base64_encode($value['foo'] ?? null),
        ];
    }

    public function __construct(private Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface $registry)
    {
    }
}

PHP
        , $class->code());
    }
}
