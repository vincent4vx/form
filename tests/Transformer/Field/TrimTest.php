<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

class TrimTest extends FormTestCase
{
    /**
     * @testWith [true]
     *           [false]
     */
    public function test_from_http(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TrimTestRequest::class) : $this->runtimeForm(TrimTestRequest::class);

        $this->assertNull($form->submit([])->value()->foo);
        $this->assertSame('a', $form->submit(['foo' => ' a  '])->value()->foo);
        $this->assertSame('a', $form->submit(['foo' => 'a'])->value()->foo);
        $this->assertSame('12.3', $form->submit(['foo' => 12.3])->value()->foo);
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_to_http_should_do_nothing(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TrimTestRequest::class) : $this->runtimeForm(TrimTestRequest::class);

        $data = new TrimTestRequest();
        $data->foo = 'a ';

        $imported = $form->import($data);

        $this->assertSame(['foo' => 'a '], $imported->httpValue());
    }

    public function test_generate_transform_from_http()
    {
        $transformer = new Trim();
        $generator = new FormTransformerGenerator($this->registry);

        $this->assertSame('is_scalar(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null)) ? trim((string) $__tmp_cf8d20da9cb97be602abb1ce003a22b3) : null', $transformer->generateTransformFromHttp($transformer, '$data["foo"] ?? null', $generator));
    }

    public function test_generate_transform_to_http()
    {
        $transformer = new Trim();
        $generator = new FormTransformerGenerator($this->registry);

        $this->assertSame('$data["foo"] ?? null', $transformer->generateTransformToHttp($transformer, '$data["foo"] ?? null', $generator));
    }

    public function test_canThrowError()
    {
        $transformer = new Trim();

        $this->assertFalse($transformer->canThrowError());
    }
}

class TrimTestRequest
{
    #[Trim]
    public ?string $foo;
}
