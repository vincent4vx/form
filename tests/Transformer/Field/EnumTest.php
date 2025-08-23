<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\View\LabelInterface;
use Quatrevieux\Form\View\LabelTrait;
use Quatrevieux\Form\View\SelectTemplate;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        $this->assertTrue($form->submit(['simple' => null])->valid());
        $this->assertNull($form->submit(['simple' => ''])->value()->simple);
        $this->assertTrue($form->submit(['simple' => ''])->valid());
        $this->assertNull($form->submit(['withString' => null])->value()->withString);
        $this->assertTrue($form->submit(['withString' => null])->valid());
        $this->assertNull($form->submit(['withString' => ''])->value()->withString);
        $this->assertTrue($form->submit(['withString' => ''])->valid());
        $this->assertNull($form->submit(['ignoreError' => 'invalid'])->value()->ignoreError);
        $this->assertTrue($form->submit(['ignoreError' => 'invalid'])->valid());
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
        $this->assertError('The value invalid is not a valid choice.', $form->submit(['withString' => new class { public function __toString() { return 'invalid'; }}])->errors()['withString']);
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
        $this->assertSame('(($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null || $__enum_4e6c78d168de10f915401b0dad567ede === \'\' ? null : ($__enum_4e6c78d168de10f915401b0dad567ede instanceof \Quatrevieux\Form\Transformer\Field\SimpleEnum ? $__enum_4e6c78d168de10f915401b0dad567ede : (is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) ? [\'Foo\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Foo, \'Bar\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Bar, \'Baz\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Baz][$__enum_4e6c78d168de10f915401b0dad567ede] ?? throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')) : throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')))))', (new Enum(SimpleEnum::class))->generateTransformFromHttp(new Enum(SimpleEnum::class), '$data["foo"]', $generator));
        $this->assertSame('(($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null || $__enum_4e6c78d168de10f915401b0dad567ede === \'\' ? null : ($__enum_4e6c78d168de10f915401b0dad567ede instanceof \Quatrevieux\Form\Transformer\Field\WithStringEnum ? $__enum_4e6c78d168de10f915401b0dad567ede : (is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) ? \Quatrevieux\Form\Transformer\Field\WithStringEnum::tryFrom((string) $__enum_4e6c78d168de10f915401b0dad567ede) ?? throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')) : throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')))))', (new Enum(SimpleEnum::class))->generateTransformFromHttp(new Enum(WithStringEnum::class), '$data["foo"]', $generator));
        $this->assertSame('(($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null || $__enum_4e6c78d168de10f915401b0dad567ede === \'\' ? null : ($__enum_4e6c78d168de10f915401b0dad567ede instanceof \Quatrevieux\Form\Transformer\Field\WithStringEnum ? $__enum_4e6c78d168de10f915401b0dad567ede : (is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) ? [\'Foo\' => \Quatrevieux\Form\Transformer\Field\WithStringEnum::Foo, \'Bar\' => \Quatrevieux\Form\Transformer\Field\WithStringEnum::Bar, \'Baz\' => \Quatrevieux\Form\Transformer\Field\WithStringEnum::Baz][$__enum_4e6c78d168de10f915401b0dad567ede] ?? throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')) : throw new \Quatrevieux\Form\Transformer\TransformerException(\'Invalid enum value\', new \Quatrevieux\Form\Validator\FieldError(\'The value {{ value }} is not a valid choice.\', [\'value\' => is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) || $__enum_4e6c78d168de10f915401b0dad567ede instanceof \Stringable ? $__enum_4e6c78d168de10f915401b0dad567ede : print_r($__enum_4e6c78d168de10f915401b0dad567ede, true)], \'052417e1-3a0d-5cd0-afdf-486cfe606edf\')))))', (new Enum(SimpleEnum::class))->generateTransformFromHttp(new Enum(WithStringEnum::class, useName: true), '$data["foo"]', $generator));
        $this->assertSame('(($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) === null || $__enum_4e6c78d168de10f915401b0dad567ede === \'\' ? null : ($__enum_4e6c78d168de10f915401b0dad567ede instanceof \Quatrevieux\Form\Transformer\Field\SimpleEnum ? $__enum_4e6c78d168de10f915401b0dad567ede : (is_scalar($__enum_4e6c78d168de10f915401b0dad567ede) ? [\'Foo\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Foo, \'Bar\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Bar, \'Baz\' => \Quatrevieux\Form\Transformer\Field\SimpleEnum::Baz][$__enum_4e6c78d168de10f915401b0dad567ede] ?? null : null)))', (new Enum(SimpleEnum::class))->generateTransformFromHttp(new Enum(SimpleEnum::class, errorOnInvalid: false), '$data["foo"]', $generator));
    }

    public function test_generateTransformToHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(!($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \Quatrevieux\Form\Transformer\Field\SimpleEnum ? null : $__enum_4e6c78d168de10f915401b0dad567ede->name)', (new Enum(SimpleEnum::class))->generateTransformToHttp(new Enum(SimpleEnum::class), '$data["foo"]', $generator));
        $this->assertSame('(!($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \Quatrevieux\Form\Transformer\Field\WithStringEnum ? null : $__enum_4e6c78d168de10f915401b0dad567ede->value)', (new Enum(SimpleEnum::class))->generateTransformToHttp(new Enum(WithStringEnum::class), '$data["foo"]', $generator));
        $this->assertSame('(!($__enum_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \Quatrevieux\Form\Transformer\Field\WithStringEnum ? null : $__enum_4e6c78d168de10f915401b0dad567ede->name)', (new Enum(SimpleEnum::class))->generateTransformToHttp(new Enum(WithStringEnum::class, useName: true), '$data["foo"]', $generator));
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_view(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TestEnumTransformerRequest::class) : $this->runtimeForm(TestEnumTransformerRequest::class);

        $this->assertEquals('<select name="simple" ><option value="Foo" >Foo</option><option value="Bar" >Bar</option><option value="Baz" >Baz</option></select>', $form->view()['simple']->render(SelectTemplate::Select));
        $this->assertEquals('<select name="simple" ><option value="Foo" >Foo</option><option value="Bar" selected>Bar</option><option value="Baz" >Baz</option></select>', $form->submit(['simple' => 'Bar'])->view()['simple']->render(SelectTemplate::Select));
        $this->assertEquals('<select name="withInt" ><option value="1" >1</option><option value="2" >2</option><option value="3" selected>3</option></select>', $form->submit(['withInt' => 3])->view()['withInt']->render(SelectTemplate::Select));
        $this->assertEquals('<select name="withLabel" ><option value="1" >Foo (1)</option><option value="2" >Bar (2)</option><option value="3" selected>Baz (3)</option></select>', $form->submit(['withLabel' => 3])->view()['withLabel']->render(SelectTemplate::Select));
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_view_translated(bool $generated)
    {
        $this->configureTranslator('fr', [
            'Foo (1)' => 'Fou (1)',
            'Bar (2)' => 'Barre (2)',
            'Baz (3)' => 'Base (3)',
        ]);
        $form = $generated ? $this->generatedForm(TestEnumTransformerRequest::class) : $this->runtimeForm(TestEnumTransformerRequest::class);

        $this->assertEquals('<select name="withLabel" ><option value="1" >Fou (1)</option><option value="2" >Barre (2)</option><option value="3" selected>Base (3)</option></select>', $form->submit(['withLabel' => 3])->view()['withLabel']->render(SelectTemplate::Select));
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

    #[Enum(WithLabel::class)]
    public ?WithLabel $withLabel;

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

enum WithLabel: int implements LabelInterface
{
    use LabelTrait;

    case Foo = 1;
    case Bar = 2;
    case Baz = 3;

    public function label(): string
    {
        return match ($this) {
            self::Foo => 'Foo (1)',
            self::Bar => 'Bar (2)',
            self::Baz => 'Baz (3)',
        };
    }
}
