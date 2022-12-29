<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
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
        $this->assertErrors([
            'validateWithInstanceMethod' => new FieldError('my error', code: '213dee40-8d06-4274-a3aa-5b21c34ab108'),
            'validateWithStaticMethod' => new FieldError('my error', code: '213dee40-8d06-4274-a3aa-5b21c34ab108'),
            'returnString' => new FieldError('my error', code: 'f90e7e91-71dd-4cca-b288-3cb74e0cb387'),
            'returnBool' => new FieldError('Custom message', code: ValidationMethod::CODE),
        ], $submitted->errors());
    }

    public function test_generate()
    {
        $generator = new ValidatorGenerator(new NullConstraintValidatorRegistry());
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->foo($data->foo ?? null, $data), \'Invalid value\', \'1b50e942-6acd-5b06-a581-d0819e7f1657\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo'), '$data->foo ?? null', $generator));
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->foo($data->foo ?? null, $data), \'other message\', \'1b50e942-6acd-5b06-a581-d0819e7f1657\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo', message: 'other message'), '$data->foo ?? null', $generator));
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError(\Quatrevieux\Form\Validator\Constraint\UtilityClass::foo($data->foo ?? null, $data), \'Invalid value\', \'1b50e942-6acd-5b06-a581-d0819e7f1657\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo', class: UtilityClass::class), '$data->foo ?? null', $generator));
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->foo($data->foo ?? null, $data, 123, \'foo\', false), \'Invalid value\', \'1b50e942-6acd-5b06-a581-d0819e7f1657\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo', parameters: [123, 'foo', false]), '$data->foo ?? null', $generator));
        $this->assertEquals('\Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->foo($data->foo ?? null, $data), \'Invalid value\', \'2418f5e6-15b9-4b4a-ab32-28acd993d945\')', (new ValidationMethod('foo'))->generate(new ValidationMethod('foo', code: '2418f5e6-15b9-4b4a-ab32-28acd993d945'), '$data->foo ?? null', $generator));
    }
}

class TestingFormWithValidationMethod
{
    #[ValidationMethod('validateWithInstanceMethod', parameters: ['bar'])]
    public ?string $validateWithInstanceMethod;

    #[ValidationMethod('validateWithStaticMethod', class: UtilityClass::class, parameters: ['bar'])]
    public ?string $validateWithStaticMethod;

    #[ValidationMethod('returnString', code: 'f90e7e91-71dd-4cca-b288-3cb74e0cb387')]
    public ?string $returnString;

    #[ValidationMethod(method: 'returnBool', message: 'Custom message')]
    public ?string $returnBool;

    public function validateWithInstanceMethod(?string $value, object $data, $bar): ?FieldError
    {
        if ($data !== $this) {
            throw new RuntimeException();
        }

        if ($value !== null && $value !== $bar) {
            return new FieldError('my error', code: '213dee40-8d06-4274-a3aa-5b21c34ab108');
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
            return new FieldError('my error', code: '213dee40-8d06-4274-a3aa-5b21c34ab108');
        }

        return null;
    }
}
