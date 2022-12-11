<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use RuntimeException;

class ValidationMethodTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional(bool $generated): void
    {
        $form = $generated ? $this->generatedForm(TestingFormWithValidationMethod::class) : $this->runtimeForm(TestingFormWithValidationMethod::class);

        $submitted = $form->submit([
            'validateWithInstanceMethod' => 'bar',
            'validateWithStaticMethod' => 'bar',
            'returnString' => 'bar',
            'returnBool' => 'bar',
        ]);
        $this->assertTrue($submitted->valid());
        $this->assertEmpty($submitted->errors());

        $submitted = $form->submit([
            'validateWithInstanceMethod' => 'a',
            'validateWithStaticMethod' => 'a',
            'returnString' => 'a',
            'returnBool' => 'a',
        ]);
        $this->assertFalse($submitted->valid());
        $this->assertEquals([
            'validateWithInstanceMethod' => new FieldError('my error'),
            'validateWithStaticMethod' => new FieldError('my error'),
            'returnString' => new FieldError('my error'),
            'returnBool' => new FieldError('Custom message'),
        ], $submitted->errors());
    }

    public function test_generate()
    {
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->foo($data->foo ?? null, $data), \'Invalid value\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo'), '$data->foo ?? null'));
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->foo($data->foo ?? null, $data), \'other message\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo', message: 'other message'), '$data->foo ?? null'));
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError(\Quatrevieux\Form\Validator\Constraint\UtilityClass::foo($data->foo ?? null, $data), \'Invalid value\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo', class: UtilityClass::class), '$data->foo ?? null'));
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->foo($data->foo ?? null, $data, 123, \'foo\', false), \'Invalid value\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo', parameters: [123, 'foo', false]), '$data->foo ?? null'));
    }
}

class TestingFormWithValidationMethod
{
    #[ValidationMethod('validateWithInstanceMethod', parameters: ['bar'])]
    public ?string $validateWithInstanceMethod;

    #[ValidationMethod('validateWithStaticMethod', class: UtilityClass::class, parameters: ['bar'])]
    public ?string $validateWithStaticMethod;

    #[ValidationMethod('returnString')]
    public ?string $returnString;

    #[ValidationMethod(method: 'returnBool', message: 'Custom message')]
    public ?string $returnBool;

    public function validateWithInstanceMethod(?string $value, object $data, $bar): ?FieldError
    {
        if ($data !== $this) {
            throw new RuntimeException();
        }

        if ($value !== null && $value !== $bar) {
            return new FieldError('my error');
        }

        return null;
    }

    public function returnString(?string $value, object $data): ?string
    {
        if ($value !== null && $value !== 'bar') {
            return 'my error';
        }

        return null;
    }

    public function returnBool(?string $value, object $data): bool
    {
        return $value === null || $value === 'bar';
    }
}

class UtilityClass
{
    public static function validateWithStaticMethod(?string $value, object $data, $bar): ?FieldError
    {
        if ($data->validateWithStaticMethod !== $value) {
            throw new RuntimeException();
        }

        if ($value !== null && $value !== $bar) {
            return new FieldError('my error');
        }

        return null;
    }
}