<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

class DefaultValueTest extends FormTestCase
{
    /**
     * @testWith [true]
     *           [false]
     */
    public function test_from_http(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TestingDefaultValue::class) : $this->runtimeForm(TestingDefaultValue::class);

        $submitted = $form->submit([]);

        $this->assertSame(12.3, $submitted->value()->explicit);
        $this->assertSame(5, $submitted->value()->usingDefaultValue);
        $this->assertSame(['12', '34', '56'], $submitted->value()->beforeTransformation);

        $submitted = $form->submit([
            'beforeTransformation' => '1,2,3',
            'usingDefaultValue' => 6,
            'explicit' => 7.8,
        ]);

        $this->assertSame(7.8, $submitted->value()->explicit);
        $this->assertSame(6, $submitted->value()->usingDefaultValue);
        $this->assertSame(['1', '2', '3'], $submitted->value()->beforeTransformation);
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_to_http_should_do_nothing(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TestingDefaultValue::class) : $this->runtimeForm(TestingDefaultValue::class);

        $data = new TestingDefaultValue();
        unset($data->beforeTransformation);
        unset($data->usingDefaultValue);
        unset($data->explicit);

        $imported = $form->import($data);

        $this->assertSame([
            'beforeTransformation' => null,
            'usingDefaultValue' => null,
            'explicit' => null,
        ], $imported->httpValue());
    }

    public function test_generate_transform_from_http()
    {
        $transformer = new DefaultValue(12.3);
        $generator = new FormTransformerGenerator($this->registry);

        $this->assertSame('$data["foo"] ?? 12.3', $transformer->generateTransformFromHttp($transformer, '$data["foo"] ?? null', $generator));
        $this->assertSame('$data["foo"] ?? null', $transformer->generateTransformFromHttp(new DefaultValue(null), '$data["foo"] ?? null', $generator));
        $this->assertSame('doTransform($data["foo"] ?? null) ?? 12.3', $transformer->generateTransformFromHttp($transformer, 'doTransform($data["foo"] ?? null)', $generator));
        $this->assertSame('doTransform($data["foo"] ?? null)', $transformer->generateTransformFromHttp(new DefaultValue(null), 'doTransform($data["foo"] ?? null)', $generator));
    }
}

class TestingDefaultValue
{
    #[DefaultValue('12,34,56')]
    #[Csv]
    public array $beforeTransformation;

    public int $usingDefaultValue = 5;

    #[DefaultValue(12.3)]
    public float $explicit = 0.0;
}
