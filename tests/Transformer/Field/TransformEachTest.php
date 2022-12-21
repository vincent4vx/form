<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\FormTestCase;

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

class Base64Transformer implements FieldTransformerInterface
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
