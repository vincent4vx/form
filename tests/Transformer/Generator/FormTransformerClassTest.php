<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\TransformationError;

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

    public function __construct(private Quatrevieux\Form\RegistryInterface $registry)
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

    public function __construct(private Quatrevieux\Form\RegistryInterface $registry)
    {
    }
}

PHP
        , $class->code());
    }

    public function test_addFieldTransformationExpression()
    {
        $class = new FormTransformerClass('ClassName');

        $class->addFieldTransformationExpression('foo', fn ($v) => "(string) ($v)", fn ($v) => "$v", false);
        $class->addFieldTransformationExpression('foo', fn ($v) => "base64_decode($v)", fn ($v) => "base64_encode($v)", false);

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

    public function __construct(private Quatrevieux\Form\RegistryInterface $registry)
    {
    }
}

PHP
        , $class->code());
    }

    public function test_addFieldTransformationExpression_with_canThrowError()
    {
        $class = new FormTransformerClass('ClassName');

        $class->addFieldTransformationExpression('foo', fn ($v) => "(string) ($v)", fn ($v) => "$v", true);
        $class->addFieldTransformationExpression('foo', fn ($v) => "base64_decode($v)", fn ($v) => "base64_encode($v)", false);

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
        $translator = $this->registry->getTranslator();

        try {
            $transformed['foo'] = base64_decode((string) ($value['foo'] ?? null));
        } catch (\Quatrevieux\Form\Transformer\TransformerException $e) {
            $errors['foo'] = $e->errors;
            $transformed['foo'] = null;
        } catch (\Exception $e) {
            $errors['foo'] = new FieldError($e->getMessage(), [], 'ec3b18d7-cb0a-5af9-b1cd-6f0b8fb00ffd', $translator);
            $transformed['foo'] = null;
        }

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'foo' => base64_encode($value['foo'] ?? null),
        ];
    }

    public function __construct(private Quatrevieux\Form\RegistryInterface $registry)
    {
    }
}

PHP
        , $class->code());
    }

    public function test_custom_error_handling()
    {
        $class = new FormTransformerClass('ClassName');

        $class->declareField('foo', 'foo', new TransformationError(message: 'my transformation error', keepOriginalValue: true, code: 'd2e95635-fdb6-4752-acb4-aa8f76f64de6'));
        $class->declareField('bar', 'bar', new TransformationError(ignore: true));

        $class->addFieldTransformationExpression('foo', fn ($v) => "(string) ($v)", fn ($v) => "$v", true);
        $class->addFieldTransformationExpression('bar', fn ($v) => "base64_decode($v)", fn ($v) => "base64_encode($v)", true);

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
        $translator = $this->registry->getTranslator();

        try {
            $transformed['foo'] = (string) ($value['foo'] ?? null);
        } catch (\Quatrevieux\Form\Transformer\TransformerException $e) {
            $errors['foo'] = $e->errors;
            $transformed['foo'] = $value['foo'] ?? null;
        } catch (\Exception $e) {
            $errors['foo'] = new FieldError('my transformation error', [], 'd2e95635-fdb6-4752-acb4-aa8f76f64de6', $translator);
            $transformed['foo'] = $value['foo'] ?? null;
        }

        try {
            $transformed['bar'] = base64_decode($value['bar'] ?? null);
        } catch (\Exception $e) {
            $transformed['bar'] = null;
        }

        return new TransformationResult($transformed, $errors);
    }

    function transformToHttp(array $value): array
    {
        return [
            'foo' => $value['foo'] ?? null,
            'bar' => base64_encode($value['bar'] ?? null),
        ];
    }

    public function __construct(private Quatrevieux\Form\RegistryInterface $registry)
    {
    }
}

PHP
        , $class->code());
    }
}
