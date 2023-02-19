<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\Constraint\Type\PrimitiveType;
use Ramsey\Uuid\Uuid;

class ArrayShapeTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(ArrayShape::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'ArrayShape')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_fixed(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayShapeTestRequest::class) : $this->runtimeForm(ArrayShapeTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['fixed' => ['foo' => 'azerty', 'bar' => 42]])->valid());

        $this->assertFalse($form->submit(['fixed' => 'foo'])->valid());
        $this->assertFalse($form->submit(['fixed' => ['foo' => 'azerty']])->valid());
        $this->assertFalse($form->submit(['fixed' => ['bar' => 42]])->valid());
        $this->assertFalse($form->submit(['fixed' => ['foo' => false, 'bar' => 42]])->valid());
        $this->assertFalse($form->submit(['fixed' => ['foo' => 'azerty', 'bar' => 4.2]])->valid());
        $this->assertFalse($form->submit(['fixed' => ['foo' => 'azerty', 'bar' => 42, 'oof' => '']])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_allowExtraKeys(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayShapeTestRequest::class) : $this->runtimeForm(ArrayShapeTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['allowExtraKeys' => ['foo' => 'azerty', 'bar' => 42]])->valid());
        $this->assertTrue($form->submit(['allowExtraKeys' => ['foo' => 'azerty', 'bar' => 42, 'oof' => '', 5 => 'aaa']])->valid());

        $this->assertFalse($form->submit(['allowExtraKeys' => 'foo'])->valid());
        $this->assertFalse($form->submit(['allowExtraKeys' => ['foo' => 'azerty']])->valid());
        $this->assertFalse($form->submit(['allowExtraKeys' => ['bar' => 42]])->valid());
        $this->assertFalse($form->submit(['allowExtraKeys' => ['foo' => false, 'bar' => 42]])->valid());
        $this->assertFalse($form->submit(['allowExtraKeys' => ['foo' => 'azerty', 'bar' => 4.2]])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_list(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayShapeTestRequest::class) : $this->runtimeForm(ArrayShapeTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['list' => []])->valid());
        $this->assertTrue($form->submit(['list' => [1.23, 125.65, 45.1]])->valid());

        $this->assertFalse($form->submit(['list' => [true, 'foo']])->valid());
        $this->assertFalse($form->submit(['list' => ['foo' => 12.3, 'bar' => 74.5]])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_table(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayShapeTestRequest::class) : $this->runtimeForm(ArrayShapeTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['table' => []])->valid());
        $this->assertTrue($form->submit(['table' => ['a' => 12, 'b' => 3.4]])->valid());

        $this->assertFalse($form->submit(['table' => ['a' => true, 'b' => 'foo']])->valid());
        $this->assertFalse($form->submit(['table' => [12, 3.4]])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_complex(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayShapeTestRequest::class) : $this->runtimeForm(ArrayShapeTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['complex' => [
            'name' => [
                'first' => 'John',
                'last' => 'Doe',
            ],
            'age' => 36,
            'address' => [
                'street' => '15 avenue du pré',
                'city' => 'La Ciotat',
                'zipCode' => 13600,
            ],
        ]])->valid());
        $this->assertTrue($form->submit(['complex' => [
            'name' => [
                'first' => 'John',
                'last' => 'Doe',
            ],
            'age' => 36,
            'address' => [
                'street' => '15 avenue du pré',
                'city' => 'La Ciotat',
                'zipCode' => '13600',
            ],
        ]])->valid());

        $this->assertFalse($form->submit(['complex' => [
            'name' => [
                'last' => 'Doe',
            ],
            'age' => 36,
            'address' => [
                'street' => '15 avenue du pré',
                'city' => 'La Ciotat',
                'zipCode' => 13600,
            ],
        ]])->valid());
        $this->assertFalse($form->submit(['complex' => [
            'name' => [
                'first' => 'John',
                'last' => 'Doe',
            ],
            'age' => 'foo',
            'address' => [
                'street' => '15 avenue du pré',
                'city' => 'La Ciotat',
                'zipCode' => 13600,
            ],
        ]])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_optional(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayShapeTestRequest::class) : $this->runtimeForm(ArrayShapeTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['optional' => ['foo' => 'azerty', 'bar' => 42]])->valid());
        $this->assertTrue($form->submit(['optional' => ['foo' => 'azerty']])->valid());
        $this->assertTrue($form->submit(['optional' => ['bar' => 42]])->valid());

        $this->assertFalse($form->submit(['optional' => 'foo'])->valid());
        $this->assertFalse($form->submit(['optional' => ['foo' => false, 'bar' => 42]])->valid());
        $this->assertFalse($form->submit(['optional' => ['foo' => 'azerty', 'bar' => 4.2]])->valid());
        $this->assertFalse($form->submit(['optional' => ['foo' => 'azerty', 'bar' => 42, 'oof' => '']])->valid());
        $this->assertFalse($form->submit(['optional' => ['foo' => null, 'bar' => 42]])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_disjonctiveNormalFormType(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayShapeTestRequest::class) : $this->runtimeForm(ArrayShapeTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['disjonctiveNormalFormType' => [new AB(), new AC(), new CC()]])->valid());

        $this->assertFalse($form->submit(['disjonctiveNormalFormType' => [new AA()]])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_arrayOfArray(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ArrayShapeTestRequest::class) : $this->runtimeForm(ArrayShapeTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['arrayOfArray' => [
            ['foo' => 'azerty', 'bar' => 42],
            ['foo' => 'qwerty', 'bar' => 24],
        ]])->valid());

        $this->assertFalse($form->submit(['arrayOfArray' => [
            ['foo' => 'azerty'],
            ['foo' => 'qwerty', 'bar' => 24],
        ]])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new ArrayShape([
            'foo' => 'string',
            'bar' => 'int',
        ], allowExtraKeys: false);

        $this->assertGeneratedValidator('($data->foo ?? null) !== null && !(is_array(($data->foo ?? null)) && ((array_key_exists(\'foo\', ($data->foo ?? null)) && is_string(($data->foo ?? null)[\'foo\']))) && ((array_key_exists(\'bar\', ($data->foo ?? null)) && is_int(($data->foo ?? null)[\'bar\']))) && array_diff_key(($data->foo ?? null), [\'foo\' => 1, \'bar\' => 1]) === []) ? new \Quatrevieux\Form\Validator\FieldError(\'This value does not match the expected array shape.\', [], \'d0909170-b496-5bb5-8cc6-efe839722a8c\') : null', $constraint);
    }
}

class ArrayShapeTestRequest
{
    #[ArrayShape([
        'foo' => 'string',
        'bar' => 'int',
    ], allowExtraKeys: false)]
    public ?array $fixed;

    #[ArrayShape([
        'foo' => 'string',
        'bar' => 'int',
    ])]
    public ?array $allowExtraKeys;

    #[ArrayShape(key: PrimitiveType::Int, value: PrimitiveType::Float)]
    public ?array $list;

    #[ArrayShape(key: 'string', value: 'float|int')]
    public ?array $table;

    #[ArrayShape(key: PrimitiveType::Int, value: [
        'foo' => PrimitiveType::String,
        'bar' => PrimitiveType::Int,
    ])]
    public ?array $arrayOfArray;

    #[ArrayShape([
        'name' => [
            'first' => PrimitiveType::String,
            'last' => PrimitiveType::String,
        ],
        'age' => PrimitiveType::Int,
        'address' => [
            'street' => 'string',
            'city' => 'string',
            'zipCode' => 'string|int',
        ],
    ])]
    public ?array $complex;

    #[ArrayShape([
        'foo?' => 'string',
        'bar?' => 'int',
    ], allowExtraKeys: false)]
    public ?array $optional;

    #[ArrayShape(value: A::class . '&' . B::class . '|' . C::class)]
    public ?array $disjonctiveNormalFormType;
}

interface A {}
interface B {}
interface C {}

class AB implements A, B {}
class AC implements A, C {}
class CC implements C {}
class AA implements A {}
