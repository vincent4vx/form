<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Validator\Constraint\Length;

class ArrayOfTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformation(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayContainer::class) : $this->runtimeForm(ArrayContainer::class);

        $value = $form->submit([
            'items' => [
                ['name' => 'foo', 'value' => '42'],
                ['name' => 'bar', 'value' => '66'],
            ],
        ])->value();

        $this->assertCount(2, $value->items);
        $this->assertSame('foo', $value->items[0]->name);
        $this->assertSame(42, $value->items[0]->value);
        $this->assertSame('bar', $value->items[1]->name);
        $this->assertSame(66, $value->items[1]->value);
        $this->assertNull($value->failling);

        $container = new ArrayContainer();
        $container->items = [];
        $container->items[] = new ArrayItem();
        $container->items[0]->name = 'abc';
        $container->items[0]->value = 741;
        $container->items[] = new ArrayItem();
        $container->items[1]->name = 'def';
        $container->items[1]->value = 852;

        $this->assertSame([
            'items' => [
                ['name' => 'abc', 'value' => 741],
                ['name' => 'def', 'value' => 852],
            ],
            'failling' => null,
        ], $form->import($container)->httpValue());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformation_error(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayContainer::class) : $this->runtimeForm(ArrayContainer::class);

        $this->assertErrors([
            'failling' => [
                0 => ['value' => 'error'],
                'foo' => ['value' => 'error'],
            ]
        ], $form->submit([
            'items' => [
                ['name' => 'foo', 'value' => '42'],
            ],
            'failling' => [
                ['value' => 'bar'],
                'foo' => ['value' => 'foo'],
            ],
        ])->errors());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_validation(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayContainer::class) : $this->runtimeForm(ArrayContainer::class);

        $this->assertTrue($form->submit([
            'items' => [
                ['name' => 'foo', 'value' => '42'],
                ['name' => 'bar', 'value' => '66'],
            ],
        ])->valid());

        $this->assertErrors(
            [
                'items' => [
                    0 => [
                        'name' => 'The value is too short. It should have 3 characters or more.',
                    ],
                    1 => [
                        'value' => 'This value is required',
                    ],
                ],
            ],
            $form->submit([
                'items' => [
                    ['name' => 'f', 'value' => '42'],
                    ['name' => 'bar'],
                ],
            ])->errors()
        );

        $this->assertErrors(
            [
                'items' => [
                    'foo' => [
                        'name' => 'The value is too short. It should have 3 characters or more.',
                    ],
                    'bar' => [
                        'value' => 'This value is required',
                    ],
                ],
            ],
            $form->submit([
                'items' => [
                    'foo' => ['name' => 'f', 'value' => '42'],
                    'bar' => ['name' => 'bar'],
                ],
            ])->errors()
        );

        $this->assertErrors(['items' => 'This value is required'], $form->submit(['items' => null])->errors());
    }

    public function test_generate()
    {
        $arrayOf = new ArrayOf(ArrayItem::class);
        /** @var FieldTransformerGeneratorInterface $transformer */
        $transformer = $arrayOf->getTransformer($registry = new ContainerRegistry($this->container));
        $generator = new FormTransformerGenerator($registry);

        $this->assertGeneratedValidator('!is_array(($data->foo ?? null)) ? null : (function ($value) {$validator = $this->registry->getValidatorFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\ArrayItem\');$errors = [];foreach ($value as $key => $item) {if ($itemErrors = $validator->validate($item)) {$errors[$key] = $itemErrors;}}return $errors ?: null;})(($data->foo ?? null))', $arrayOf);
        $this->assertSame('!is_array($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null) ? null : (function ($value) {$transformer = $this->registry->getTransformerFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\ArrayItem\');$instantiator = $this->registry->getInstantiatorFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\ArrayItem\');$result = [];$errors = [];foreach ($value as $key => $item) {$transformationResult = $transformer->transformFromHttp((array) $item);if ($transformationResult->errors) {$errors[$key] = $transformationResult->errors;} else {$result[$key] = $instantiator->instantiate($transformationResult->values);}}if ($errors) {throw new \Quatrevieux\Form\Transformer\TransformerException(\'Some elements are invalid\', $errors);}return $result;})($__tmp_44e18f0f3b2a419fae74cbbaef66f40e)', $transformer->generateTransformFromHttp($arrayOf, '$data->foo ?? null', $generator));
        $this->assertSame('!is_array($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null) ? null : (function ($value) {$transformer = $this->registry->getTransformerFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\ArrayItem\');$instantiator = $this->registry->getInstantiatorFactory()->create(\'Quatrevieux\\\Form\\\Embedded\\\ArrayItem\');$result = [];foreach ($value as $key => $item) {$result[$key] = $transformer->transformToHttp($instantiator->export($item));}return $result;})($__tmp_44e18f0f3b2a419fae74cbbaef66f40e)', $transformer->generateTransformToHttp($arrayOf, '$data->foo ?? null', $generator));
    }
}

class ArrayContainer
{
    #[ArrayOf(ArrayItem::class)]
    public array $items;

    #[ArrayOf(FallingItem::class)]
    public ?array $failling;
}

class ArrayItem
{
    #[Length(min: 3)]
    public string $name;
    public int $value;
}

class FallingItem
{
    #[FallingTransformer]
    public mixed $value;
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FallingTransformer implements FieldTransformerInterface
{
    public function transformFromHttp(mixed $value): mixed
    {
        throw new \RuntimeException('error');
    }

    public function transformToHttp(mixed $value): mixed
    {
        return $value;
    }

    public function canThrowError(): bool
    {
        return true;
    }
}