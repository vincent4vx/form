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

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class ClassName implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
        ];
        return new TransformationResult($transformed, $errors);
    }

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

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class ClassName implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'foo' => $value['bar'] ?? null,
            'baz' => $value['rab'] ?? null,
        ];
        return new TransformationResult($transformed, $errors);
    }

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

use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Validator\FieldError;

class ClassName implements Quatrevieux\Form\Transformer\FormTransformerInterface
{
    function transformFromHttp(array $value): TransformationResult
    {
        $errors = [];
        $transformed = [
            'foo' => base64_decode((string) ($value['foo'] ?? null)),
        ];
        return new TransformationResult($transformed, $errors);
    }

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
