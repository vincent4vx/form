<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

class JsonTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TestJsonTransformerRequest::class) : $this->runtimeForm(TestJsonTransformerRequest::class);

        $this->assertNull($form->submit(['default' => null])->value()->default);

        $this->assertSame(42, $form->submit(['default' => '42'])->value()->default);
        $this->assertSame(4.2, $form->submit(['default' => '4.2'])->value()->default);
        $this->assertSame('foo', $form->submit(['default' => '"foo"'])->value()->default);
        $this->assertSame(true, $form->submit(['default' => 'true'])->value()->default);
        $this->assertSame(['foo' => 123, 'bar' => true], $form->submit(['default' => '{"foo":123,"bar":true}'])->value()->default);
        $this->assertSame([true, 456], $form->submit(['default' => '[true, 456]'])->value()->default);
        $this->assertSame(['foo' => 123, 'bar' => true], $form->submit(['maxDepth' => '{"foo":123,"bar":true}'])->value()->maxDepth);
        $this->assertEquals((object) ['foo' => 123, 'bar' => true], $form->submit(['asObject' => '{"foo":123,"bar":true}'])->value()->asObject);
        $this->assertSame(12345678901234567890, $form->submit(['default' => '12345678901234567890'])->value()->default);
        $this->assertSame('12345678901234567890', $form->submit(['parseOptions' => '12345678901234567890'])->value()->parseOptions);

        $this->assertNull($form->submit(['default' => '{invalid}'])->value()->default);
        $this->assertError('The value is not a valid JSON : Syntax error', $form->submit(['default' => '{invalid}'])->errors()['default']);
        $this->assertError('The value must be a string', $form->submit(['default' => []])->errors()['default']);
        $this->assertError('The value is not a valid JSON : Maximum stack depth exceeded', $form->submit(['maxDepth' => '{"foo":[true]}'])->errors()['maxDepth']);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformToHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TestJsonTransformerRequest::class) : $this->runtimeForm(TestJsonTransformerRequest::class);

        $this->assertNull($form->import(TestJsonTransformerRequest::default(null))->httpValue()['default']);
        $this->assertSame('42', $form->import(TestJsonTransformerRequest::default(42))->httpValue()['default']);
        $this->assertSame('4.2', $form->import(TestJsonTransformerRequest::default(4.2))->httpValue()['default']);
        $this->assertSame('"foo"', $form->import(TestJsonTransformerRequest::default('foo'))->httpValue()['default']);
        $this->assertSame('true', $form->import(TestJsonTransformerRequest::default(true))->httpValue()['default']);
        $this->assertSame('{"foo":123,"bar":true}', $form->import(TestJsonTransformerRequest::default(['foo' => 123, 'bar' => true]))->httpValue()['default']);
        $this->assertSame('[true,456]', $form->import(TestJsonTransformerRequest::default([true, 456]))->httpValue()['default']);
        $this->assertSame('{"foo":"http:\/\/foo.bar","bar":"h\u00e9risson"}', $form->import(TestJsonTransformerRequest::default(['foo' => 'http://foo.bar', 'bar' => 'hérisson']))->httpValue()['default']);
        $this->assertSame(<<<'JSON'
            {
                "foo": "http://foo.bar",
                "bar": "hérisson"
            }
            JSON,
            $form->import(TestJsonTransformerRequest::pretty(['foo' => 'http://foo.bar', 'bar' => 'hérisson']))->httpValue()['pretty']
        );
    }

    public function test_generateTransformFromHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null ? null : (!is_scalar($__tmp_4e6c78d168de10f915401b0dad567ede) ? throw new \InvalidArgumentException(\'The value must be a string\') : (!($__tmp_4e6c78d168de10f915401b0dad567ede = json_decode($__tmp_4e6c78d168de10f915401b0dad567ede, true, 512, 2)) && json_last_error() !== JSON_ERROR_NONE ? throw new \InvalidArgumentException(\'The value is not a valid JSON : \' . json_last_error_msg()) : $__tmp_4e6c78d168de10f915401b0dad567ede))', (new Json())->generateTransformFromHttp(new Json(parseOptions: JSON_BIGINT_AS_STRING), '$data["foo"]', $generator));
    }

    public function test_generateTransformToHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null ? null : json_encode($__tmp_4e6c78d168de10f915401b0dad567ede, 448)', (new Json())->generateTransformToHttp(new Json(encodeOptions: JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), '$data["foo"]', $generator));
    }
}

class TestJsonTransformerRequest
{
    #[Json]
    public mixed $default;

    #[Json(depth: 2)]
    public mixed $maxDepth;

    #[Json(assoc: false)]
    public mixed $asObject;

    #[Json(parseOptions: JSON_BIGINT_AS_STRING)]
    public mixed $parseOptions;

    #[Json(encodeOptions: JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)]
    public mixed $pretty;

    public static function default($value): self
    {
        $self = new self();
        $self->default = $value;

        return $self;
    }
    public static function pretty($value): self
    {
        $self = new self();
        $self->pretty = $value;

        return $self;
    }
}
