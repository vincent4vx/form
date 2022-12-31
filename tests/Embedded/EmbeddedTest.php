<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\ArrayCast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\Csv;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Validator\Constraint\Length;

class EmbeddedTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_transformation(bool $generated)
    {
        $form = $generated ? $this->generatedForm(BaseForm::class) : $this->runtimeForm(BaseForm::class);

        $submitted = $form->submit([
            'name' => 'foo',
            'value' => '42',
            'embedded' => [
                'foo' => 'azerty',
                'bar' => '4,2,6',
            ],
        ]);

        $this->assertSame('foo', $submitted->value()->name);
        $this->assertSame(42, $submitted->value()->value);
        $this->assertInstanceOf(EmbeddedForm::class, $submitted->value()->embedded);
        $this->assertSame('azerty', $submitted->value()->embedded->foo);
        $this->assertSame([4, 2, 6], $submitted->value()->embedded->bar);
        $this->assertNull($submitted->value()->optionalEmbedded);

        $submitted = $form->submit([
            'name' => 'foo',
            'value' => '42',
            'embedded' => [
                'foo' => 'azerty',
                'bar' => '4,2,6',
            ],
            'optionalEmbedded' => [
                'foo' => 'aqwzsx',
                'bar' => '7,4,1',
            ],
        ]);

        $this->assertSame('foo', $submitted->value()->name);
        $this->assertSame(42, $submitted->value()->value);
        $this->assertInstanceOf(EmbeddedForm::class, $submitted->value()->embedded);
        $this->assertSame('azerty', $submitted->value()->embedded->foo);
        $this->assertSame([4, 2, 6], $submitted->value()->embedded->bar);
        $this->assertInstanceOf(EmbeddedForm::class, $submitted->value()->optionalEmbedded);
        $this->assertSame('aqwzsx', $submitted->value()->optionalEmbedded->foo);
        $this->assertSame([7, 4, 1], $submitted->value()->optionalEmbedded->bar);

        $data = new BaseForm();
        $data->name = 'bar';
        $data->value = 666;
        $data->embedded = new EmbeddedForm();
        $data->embedded->foo = 'qwerty';
        $data->embedded->bar = [1, 2, 3];

        $this->assertSame([
            'name' => 'bar',
            'value' => 666,
            'embedded' => [
                'foo' => 'qwerty',
                'bar' => '1,2,3',
            ],
            'optionalEmbedded' => null,
        ], $form->import($data)->httpValue());

        $data->optionalEmbedded = new EmbeddedForm();
        $data->optionalEmbedded->foo = 'aqwzsx';
        $data->optionalEmbedded->bar = [6, 6, 6];

        $this->assertSame([
            'name' => 'bar',
            'value' => 666,
            'embedded' => [
                'foo' => 'qwerty',
                'bar' => '1,2,3',
            ],
            'optionalEmbedded' => [
                'foo' => 'aqwzsx',
                'bar' => '6,6,6',
            ],
        ], $form->import($data)->httpValue());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_validation(bool $generated)
    {
        $form = $generated ? $this->generatedForm(BaseForm::class) : $this->runtimeForm(BaseForm::class);

        $this->assertErrors([
            'name' => 'This value is required',
            'value' => 'This value is required',
            'embedded' => 'This value is required',
        ], $form->submit([])->errors());

        $this->assertErrors([
            'embedded' => [
                'foo' => 'This value is required',
                'bar' => 'This value is required',
            ],
            'optionalEmbedded' => [
                'foo' => 'This value is required',
                'bar' => 'This value is required',
            ]
        ], $form->submit([
            'name' => 'foo',
            'value' => '42',
            'embedded' => [],
            'optionalEmbedded' => [],
        ])->errors());

        $this->assertErrors([
            'embedded' => [
                'foo' => 'The value length is invalid. It should be between 3 and 5 characters long.',
            ],
            'optionalEmbedded' => [
                'foo' => 'The value length is invalid. It should be between 3 and 5 characters long.',
            ]
        ], $form->submit([
            'name' => 'foo',
            'value' => '42',
            'embedded' => [
                'foo' => 'ab',
                'bar' => '2,3',
            ],
            'optionalEmbedded' => [
                'foo' => 'abcdefg',
                'bar' => '3,4',
            ],
        ])->errors());

        $this->assertEmpty($form->submit([
            'name' => 'foo',
            'value' => '42',
            'embedded' => [
                'foo' => 'abc',
                'bar' => '2,3',
            ],
        ])->errors());

        $this->configureTranslator('fr', [
            'This value is required' => 'Ce champ est requis',
            'The value length is invalid. It should be between {{ min }} and {{ max }} characters long.' => 'La longueur de la valeur est invalide. Elle doit être comprise entre {{ min }} et {{ max }} caractères.',
        ]);

        $this->assertErrors([
            'embedded' => [
                'foo' => 'La longueur de la valeur est invalide. Elle doit être comprise entre 3 et 5 caractères.',
                'bar' => 'Ce champ est requis',
            ],
        ], $form->submit([
            'name' => 'foo',
            'value' => '42',
            'embedded' => [
                'foo' => 'ab',
            ],
        ])->errors());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_recursive(bool $generated)
    {
        $form = $generated ? $this->generatedForm(RecursiveForm::class) : $this->runtimeForm(RecursiveForm::class);

        $submitted = $form->submit(['name' => 'foo']);
        $this->assertTrue($submitted->valid());
        $this->assertSame('foo', $submitted->value()->name);
        $this->assertNull($submitted->value()->child);

        $submitted = $form->submit(['name' => 'foo', 'child' => ['name' => 'bar']]);
        $this->assertTrue($submitted->valid());
        $this->assertSame('foo', $submitted->value()->name);
        $this->assertSame('bar', $submitted->value()->child->name);
        $this->assertNull($submitted->value()->child->child);

        $submitted = $form->submit([
            'name' => 'foo',
            'child' => [
                'name' => 'bar',
                'child' => [
                    'name' => 'baz',
                    'child' => [
                        'name' => 'qux',
                        'child' => [
                            'name' => 'quux',
                        ],
                    ],
                ],
            ]
        ]);
        $this->assertTrue($submitted->valid());
        $this->assertSame('foo', $submitted->value()->name);
        $this->assertSame('bar', $submitted->value()->child->name);
        $this->assertSame('baz', $submitted->value()->child->child->name);
        $this->assertSame('qux', $submitted->value()->child->child->child->name);
        $this->assertSame('quux', $submitted->value()->child->child->child->child->name);
        $this->assertNull($submitted->value()->child->child->child->child->child);

        $this->assertSame([
            'name' => 'foo',
            'child' => [
                'name' => 'bar',
                'child' => [
                    'name' => 'baz',
                    'child' => [
                        'name' => 'qux',
                        'child' => [
                            'name' => 'quux',
                            'child' => null,
                        ],
                    ],
                ],
            ]
        ], $form->import($submitted->value())->httpValue());
    }

    public function test_generate_validator()
    {
        $validator = new Embedded(EmbeddedForm::class);
        $this->assertGeneratedValidator('is_object(($data->foo ?? null)) ? $this->registry->getValidatorFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\EmbeddedForm\')->validate(($data->foo ?? null)) : null', $validator);
    }

    public function test_generate_from_http()
    {
        $transformer = new Embedded(EmbeddedForm::class);
        $this->assertSame('is_array($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) ? $this->registry->getInstantiatorFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\EmbeddedForm\')->instantiate($this->registry->getTransformerFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\EmbeddedForm\')->transformFromHttp($__tmp_4e6c78d168de10f915401b0dad567ede)->values) : null', $transformer->getTransformer($this->registry)->generateTransformFromHttp($transformer, '$data["foo"]', new FormTransformerGenerator($this->registry)));
    }

    public function test_generate_to_http()
    {
        $transformer = new Embedded(EmbeddedForm::class);
        $this->assertSame('is_object($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) ? $this->registry->getTransformerFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\EmbeddedForm\')->transformToHttp($this->registry->getInstantiatorFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\EmbeddedForm\')->export($__tmp_4e6c78d168de10f915401b0dad567ede)) : null', $transformer->getTransformer($this->registry)->generateTransformToHttp($transformer, '$data["foo"]', new FormTransformerGenerator($this->registry)));
    }
}

class BaseForm
{
    public string $name;
    public int $value;

    #[Embedded(EmbeddedForm::class)]
    public EmbeddedForm $embedded;

    #[Embedded(EmbeddedForm::class)]
    public ?EmbeddedForm $optionalEmbedded;
}

class EmbeddedForm
{
    #[Length(min: 3, max: 5)]
    public string $foo;

    #[Csv, ArrayCast(CastType::Int)]
    public array $bar;
}

class RecursiveForm
{
    public string $name;

    #[Embedded(RecursiveForm::class)]
    public ?RecursiveForm $child;
}
