<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Ramsey\Uuid\Uuid;

class EnumTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(Enum::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'Enum')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TestEnumTransformerRequest::class) : $this->runtimeForm(TestEnumTransformerRequest::class);

        $this->assertNull($form->submit(['simple' => null])->value()->simple);
        $this->assertNull($form->submit(['withString' => null])->value()->withString);
        $this->assertNull($form->submit(['ignoreError' => 'invalid'])->value()->ignoreError);
        $this->assertNull($form->submit(['ignoreError' => []])->value()->ignoreError);

        $this->assertSame(SimpleEnum::Foo, $form->submit(['simple' => 'Foo'])->value()->simple);
        $this->assertSame(SimpleEnum::Bar, $form->submit(['simple' => 'Bar'])->value()->simple);
        $this->assertSame(SimpleEnum::Baz, $form->submit(['simple' => 'Baz'])->value()->simple);
        $this->assertSame(SimpleEnum::Baz, $form->submit(['simple' => SimpleEnum::Baz])->value()->simple);
        $this->assertSame(WithStringEnum::Foo, $form->submit(['withString' => 'foo'])->value()->withString);
        $this->assertSame(WithStringEnum::Bar, $form->submit(['useName' => 'Bar'])->value()->useName);
        $this->assertSame(WithIntEnum::Baz, $form->submit(['withInt' => '3'])->value()->withInt);
        $this->assertSame(WithIntEnum::Baz, $form->submit(['withInt' => 3.0])->value()->withInt);
        $this->assertSame(WithIntEnum::Foo, $form->submit(['withInt' => true])->value()->withInt);

        $this->assertNull($form->submit(['simple' => 'Oof'])->value()->simple);
        $this->assertSame(Enum::CODE, $form->submit(['simple' => 'Oof'])->errors()['simple']->code);
        $this->assertError('The value Oof is not a valid choice.', $form->submit(['simple' => 'Oof'])->errors()['simple']);
        $this->assertError("The value Array\n(\n)\n is not a valid choice.", $form->submit(['simple' => []])->errors()['simple']);
        $this->assertError('The value Foo is not a valid choice.', $form->submit(['withString' => 'Foo'])->errors()['withString']);
        $this->assertError('custom message', $form->submit(['customMessage' => 'Oof'])->errors()['customMessage']);
        $this->assertError(<<<'ERR'
        The value Quatrevieux\Form\Transformer\Field\WithStringEnum Enum:string
        (
            [name] => Baz
            [value] => baz
        )
         is not a valid choice.
        ERR, $form->submit(['simple' => WithStringEnum::Baz])->errors()['simple']);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformToHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TestEnumTransformerRequest::class) : $this->runtimeForm(TestEnumTransformerRequest::class);

        $req = new TestEnumTransformerRequest();
        $this->assertNull($form->import($req)->httpValue()['simple']);

        $req->simple = SimpleEnum::Foo;
        $this->assertSame('Foo', $form->import($req)->httpValue()['simple']);

        $req->withString = WithStringEnum::Baz;
        $this->assertSame('baz', $form->import($req)->httpValue()['withString']);

        $req->withInt = WithIntEnum::Bar;
        $this->assertSame(2, $form->import($req)->httpValue()['withInt']);

        $req->useName = WithStringEnum::Bar;
        $this->assertSame('Bar', $form->import($req)->httpValue()['useName']);
    }

    public function test_generateTransformFromHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null ? null : ($__enum_4e6c78d168de10f915401b0dad567ede instanceof \Quatrevieux\Form\Transformer\Field\SimpleEnum ? $__enum_4e6c78d168de10f915401b0dad567ede : (is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) ? [\'Foo\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Foo, \'Bar\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Bar, \'Baz\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Baz][$__enum_4e6c78d168de10f915401b0dad567ede] ?? throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')) : throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')))))', (new Enum(SimpleEnum::class))->generateTransformFromHttp(new Enum(SimpleEnum::class), '$data["foo"]', $generator));
        $this->assertSame('(($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null ? null : ($__enum_4e6c78d168de10f915401b0dad567ede instanceof \Quatrevieux\Form\Transformer\Field\WithStringEnum ? $__enum_4e6c78d168de10f915401b0dad567ede : (is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) ? \Quatrevieux\Form\Transformer\Field\WithStringEnum::tryFrom((string) $__enum_4e6c78d168de10f915401b0dad567ede) ?? throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')) : throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')))))', (new Enum(SimpleEnum::class))->generateTransformFromHttp(new Enum(WithStringEnum::class), '$data["foo"]', $generator));
        $this->assertSame('(($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null ? null : ($__enum_4e6c78d168de10f915401b0dad567ede instanceof \Quatrevieux\Form\Transformer\Field\WithStringEnum ? $__enum_4e6c78d168de10f915401b0dad567ede : (is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) ? [\'Foo\' => \Quatrevieux\Form\Transformer\Field\WithStringEnum::Foo, \'Bar\' => \Quatrevieux\Form\Transformer\Field\WithStringEnum::Bar, \'Baz\' => \Quatrevieux\Form\Transformer\Field\WithStringEnum::Baz][$__enum_4e6c78d168de10f915401b0dad567ede] ?? throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')) : throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')))))', (new Enum(SimpleEnum::class))->generateTransformFromHttp(new Enum(WithStringEnum::class, useName: true), '$data["foo"]', $generator));
        $this->assertSame('(($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null ? null : ($__enum_4e6c78d168de10f915401b0dad567ede instanceof \Quatrevieux\Form\Transformer\Field\SimpleEnum ? $__enum_4e6c78d168de10f915401b0dad567ede : (is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) ? [\'Foo\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Foo, \'Bar\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Bar, \'Baz\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Baz][$__enum_4e6c78d168de10f915401b0dad567ede] ?? null : null)))', (new Enum(SimpleEnum::class))->generateTransformFromHttp(new Enum(SimpleEnum::class, errorOnInvalid: false), '$data["foo"]', $generator));
    }

    public function test_generateTransformToHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(!($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \Quatrevieux\Form\Transformer\Field\SimpleEnum ? null : $__enum_4e6c78d168de10f915401b0dad567ede->name)', (new Enum(SimpleEnum::class))->generateTransformToHttp(new Enum(SimpleEnum::class), '$data["foo"]', $generator));
        $this->assertSame('(!($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \Quatrevieux\Form\Transformer\Field\WithStringEnum ? null : $__enum_4e6c78d168de10f915401b0dad567ede->value)', (new Enum(SimpleEnum::class))->generateTransformToHttp(new Enum(WithStringEnum::class), '$data["foo"]', $generator));
        $this->assertSame('(!($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \Quatrevieux\Form\Transformer\Field\WithStringEnum ? null : $__enum_4e6c78d168de10f915401b0dad567ede->name)', (new Enum(SimpleEnum::class))->generateTransformToHttp(new Enum(WithStringEnum::class, useName: true), '$data["foo"]', $generator));
    }
}

class TestEnumTransformerRequest
{
    #[Enum(SimpleEnum::class)]
    public ?SimpleEnum $simple;

    #[Enum(WithStringEnum::class)]
    public ?WithStringEnum $withString;

    #[Enum(WithIntEnum::class)]
    public ?WithIntEnum $withInt;

    #[Enum(WithStringEnum::class, useName: true)]
    public ?WithStringEnum $useName;

    #[Enum(SimpleEnum::class, errorOnInvalid: false)]
    public ?SimpleEnum $ignoreError;

    #[Enum(SimpleEnum::class, errorMessage: 'custom message')]
    public ?SimpleEnum $customMessage;
}

enum SimpleEnum
{
    case Foo;
    case Bar;
    case Baz;
}

enum WithStringEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
    case Baz = 'baz';
}

enum WithIntEnum: int
{
    case Foo = 1;
    case Bar = 2;
    case Baz = 3;
}
