<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

class CastTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CastTestRequest::class) : $this->runtimeForm(CastTestRequest::class);

        $this->assertSame(42, $form->submit(['value' => '42'])->value()->value);
        $this->assertSame(4, $form->submit(['value' => '4.2'])->value()->value);
        $this->assertSame(0, $form->submit(['value' => 'foo'])->value()->value);

        $this->assertNull($form->submit(['value' => null])->value()->value);
        $this->assertNull($form->submit(['value' => []])->value()->value);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformToHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CastTestRequest::class) : $this->runtimeForm(CastTestRequest::class);

        $this->assertSame(42, $form->import(CastTestRequest::create(42))->httpValue()['value']);
        $this->assertSame('42', $form->import(CastTestRequest::create('42'))->httpValue()['value']);
        $this->assertSame(null, $form->import(CastTestRequest::create(null))->httpValue()['value']);
        $this->assertSame(['foo' => 'bar'], $form->import(CastTestRequest::create((object) ['foo' => 'bar']))->httpValue()['value']);
    }

    public function test_generateTransformFromHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(is_scalar($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) ? (int) $__tmp_4e6c78d168de10f915401b0dad567ede : null)', (new Cast(CastType::Int))->generateTransformFromHttp(new Cast(CastType::Int), '$data["foo"]', $generator));
    }

    public function test_generateTransformToHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null || is_scalar($__tmp_4e6c78d168de10f915401b0dad567ede) ? $__tmp_4e6c78d168de10f915401b0dad567ede : (array) $__tmp_4e6c78d168de10f915401b0dad567ede)', (new Cast(CastType::Int))->generateTransformToHttp(new Cast(CastType::Int), '$data["foo"]', $generator));
    }
}

class CastTestRequest
{
    #[Cast(CastType::Int)]
    public mixed $value;

    public static function create($value)
    {
        $t = new self();
        $t->value = $value;

        return $t;
    }
}
