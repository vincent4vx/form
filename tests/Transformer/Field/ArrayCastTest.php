<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\FormTestCase;

class ArrayCastTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp_keep_keys(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayCastTestRequest::class) : $this->runtimeForm(ArrayCastTestRequest::class);

        $this->assertSame([42], $form->submit(['keepKeys' => '42'])->value()->keepKeys);
        $this->assertSame([42], $form->submit(['keepKeys' => ['42']])->value()->keepKeys);
        $this->assertSame(['foo' => 123, 'bar' => 456], $form->submit(['keepKeys' => ['foo' => '123', 'bar' => '456']])->value()->keepKeys);
        $this->assertSame([42, 0, null, 123, 456], $form->submit(['keepKeys' => ['42', 'foo', null, '123', 456]])->value()->keepKeys);

        $this->assertNull($form->submit(['keepKeys' => null])->value()->keepKeys);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp_reset_keys(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayCastTestRequest::class) : $this->runtimeForm(ArrayCastTestRequest::class);

        $this->assertSame([42.0], $form->submit(['resetKeys' => '42'])->value()->resetKeys);
        $this->assertSame([4.2], $form->submit(['resetKeys' => ['4.2']])->value()->resetKeys);
        $this->assertSame([12.3, 45.6], $form->submit(['resetKeys' => ['foo' => '12.3', 'bar' => '45.6']])->value()->resetKeys);
        $this->assertSame([42.0, 0.0, null, 12.3, 456.0], $form->submit(['resetKeys' => ['42', 'foo', null, '12.3', 456]])->value()->resetKeys);

        $this->assertNull($form->submit(['resetKeys' => null])->value()->resetKeys);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformToHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayCastTestRequest::class) : $this->runtimeForm(ArrayCastTestRequest::class);

        $o = new ArrayCastTestRequest();

        $o->keepKeys = ['foo' => 'bar', 42];
        $this->assertSame(['foo' => 'bar', 42], $form->import($o)->httpValue()['keepKeys']);

        $o->keepKeys = null;
        $this->assertNull($form->import($o)->httpValue()['keepKeys']);
    }

    public function test_generateTransformFromHttp()
    {
        $this->assertSame('(($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) !== null ? array_map(static fn ($value) => (is_scalar($__tmp_7d0596c36891967f3bb9d994b4a97c19 = $value) ? (int) $__tmp_7d0596c36891967f3bb9d994b4a97c19 : null), (array) $__tmp_4e6c78d168de10f915401b0dad567ede) : null)', (new ArrayCast(CastType::Int))->generateTransformFromHttp(new ArrayCast(CastType::Int), '$data["foo"]'));
        $this->assertSame('(($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) !== null ? (static function ($values) { $r = []; foreach ($values as $value) { $r[] = (is_scalar($__tmp_7d0596c36891967f3bb9d994b4a97c19 = $value) ? (int) $__tmp_7d0596c36891967f3bb9d994b4a97c19 : null); } return $r; })((array) $__tmp_4e6c78d168de10f915401b0dad567ede) : null)', (new ArrayCast(CastType::Int))->generateTransformFromHttp(new ArrayCast(CastType::Int, false), '$data["foo"]'));
    }

    public function test_generateTransformToHttp()
    {
        $this->assertSame('(($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) !== null ? (array) $__tmp_4e6c78d168de10f915401b0dad567ede : null)', (new ArrayCast(CastType::Int))->generateTransformToHttp(new ArrayCast(CastType::Int), '$data["foo"]'));
        $this->assertSame('(($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) !== null ? (array) $__tmp_4e6c78d168de10f915401b0dad567ede : null)', (new ArrayCast(CastType::Int))->generateTransformToHttp(new ArrayCast(CastType::Int, false), '$data["foo"]'));
    }
}

class ArrayCastTestRequest
{
    #[ArrayCast(CastType::Int)]
    public ?array $keepKeys;

    #[ArrayCast(CastType::Float, preserveKeys: false)]
    public ?array $resetKeys;
}