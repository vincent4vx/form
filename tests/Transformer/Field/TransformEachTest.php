<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\Code;

class TransformEachTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TransformEachTesting::class) : $this->runtimeForm(TransformEachTesting::class);

        $this->assertNull($form->submit([])->value()->values);
        $this->assertSame([], $form->submit(['values' => []])->value()->values);

        $this->assertSame([['foo' => 'bar']], $form->submit(['values' => 'eyJmb28iOiJiYXIifQ=='])->value()->values);
        $this->assertSame(['a' => ['foo' => 'bar']], $form->submit(['values' => ['a' => 'eyJmb28iOiJiYXIifQ==']])->value()->values);
        $this->assertSame([['foo' => 'bar'], ['firstName' => 'John', 'lastName' => 'Doe']], $form->submit(['values' => ['eyJmb28iOiJiYXIifQ==', 'eyJmaXJzdE5hbWUiOiJKb2huIiwibGFzdE5hbWUiOiJEb2UifQ==']])->value()->values);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformToHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TransformEachTesting::class) : $this->runtimeForm(TransformEachTesting::class);

        $this->assertNull($form->import(new TransformEachTesting())->httpValue()['values']);
        $this->assertSame([], $form->import(new TransformEachTesting([]))->httpValue()['values']);
        $this->assertSame(['eyJmb28iOiJiYXIifQ=='], $form->import(new TransformEachTesting([['foo' => 'bar']]))->httpValue()['values']);
        $this->assertSame(['eyJmb28iOiJiYXIifQ==', 'eyJmaXJzdE5hbWUiOiJKb2huIiwibGFzdE5hbWUiOiJEb2UifQ=='], $form->import(new TransformEachTesting([['foo' => 'bar'], ['firstName' => 'John', 'lastName' => 'Doe']]))->httpValue()['values']);
        $this->assertSame(['a' => 'eyJmb28iOiJiYXIifQ=='], $form->import(new TransformEachTesting(['a' => ['foo' => 'bar']]))->httpValue()['values']);
    }

    public function test_generate()
    {
        $transformer = new TransformEach([
            new Base64Transformer(),
            new JsonTransformer(),
        ]);
        $generator = new FormTransformerGenerator(new NullFieldTransformerRegistry());

        $this->assertSame('($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null) === null ? null : \array_map(fn ($item) => (new \Quatrevieux\Form\Transformer\Field\JsonTransformer())->transformFromHttp((($__tmp_0f8134fb6038ebcd7155f1de5f067c73 = ($item)) ? base64_decode($__tmp_0f8134fb6038ebcd7155f1de5f067c73) : null)), (array) $__tmp_cf8d20da9cb97be602abb1ce003a22b3)', $transformer->getTransformer(new NullFieldTransformerRegistry())->generateTransformFromHttp($transformer, '$data["foo"] ?? null', $generator));
        $this->assertSame('($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null) === null ? null : \array_map(fn ($item) => (($__tmp_05f1b0308b35161ae3bf8b9998e27763 = ((new \Quatrevieux\Form\Transformer\Field\JsonTransformer())->transformToHttp($item))) ? base64_encode($__tmp_05f1b0308b35161ae3bf8b9998e27763) : null), (array) $__tmp_cf8d20da9cb97be602abb1ce003a22b3)', $transformer->getTransformer(new NullFieldTransformerRegistry())->generateTransformToHttp($transformer, '$data["foo"] ?? null', $generator));
    }
}

class TransformEachTesting
{
    #[TransformEach([
        new Base64Transformer(),
        new JsonTransformer(),
    ])]
    public ?array $values;

    /**
     * @param array|null $values
     */
    public function __construct(?array $values = null)
    {
        $this->values = $values;
    }
}

class Base64Transformer implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function transformFromHttp(mixed $value): ?string
    {
        return $value ? base64_decode($value) : null;
    }

    public function transformToHttp(mixed $value): ?string
    {
        return $value ? base64_encode($value) : null;
    }

    public function canThrowError(): bool
    {
        return false;
    }

    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);

        return "(({$varName} = ({$previousExpression})) ? base64_decode({$varName}) : null)";
    }

    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);

        return "(({$varName} = ({$previousExpression})) ? base64_encode({$varName}) : null)";
    }
}

class JsonTransformer implements FieldTransformerInterface
{
    public function transformFromHttp(mixed $value): mixed
    {
        return $value ? json_decode($value, true) : null;
    }

    public function transformToHttp(mixed $value): mixed
    {
        return $value === null ? null : json_encode($value);
    }

    public function canThrowError(): bool
    {
        return false;
    }
}
