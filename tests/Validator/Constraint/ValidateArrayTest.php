<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

class ValidateArrayTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional(bool $generated)
    {
        $form = $generated ? $this->generatedForm(TestingValidateArray::class) : $this->runtimeForm(TestingValidateArray::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['values' => ['123', '345']])->valid());
        $this->assertFalse($form->submit(['values' => ['12', '34']])->valid());
        $this->assertFalse($form->submit(['values' => ['abc']])->valid());

        $this->assertEquals(<<<'ERROR'
Some values are invalid :
- On item 0: Invalid length
- On item 1: Invalid length

ERROR
            , (string) $form->submit(['values' => ['12', '34']])->errors()['values']);

        $this->assertEquals(<<<'ERROR'
Some values are invalid :
- On item 1: Invalid length

ERROR
            , (string) $form->submit(['values' => ['122', '34']])->errors()['values']);

        $this->assertEquals(<<<'ERROR'
Some values are invalid :
- On item bar: Invalid length

ERROR
            , (string) $form->submit(['values' => ['foo' => '122', 'bar' => '34']])->errors()['values']);

        $this->assertEquals('My error', (string) $form->submit(['withoutPerItemError' => ['1', '2']])->errors()['withoutPerItemError']);

        $this->assertEquals([
            0 => new FieldError('Invalid length'),
            1 => new FieldError('Invalid length'),
        ], $form->submit(['arrayOfErrors' => ['12', '34']])->errors()['arrayOfErrors']);

        $this->assertEquals([
            1 => new FieldError('Invalid length'),
        ], $form->submit(['arrayOfErrors' => ['123', '34']])->errors()['arrayOfErrors']);

        $this->assertTrue($form->submit(['arrayOfErrors' => ['123', '345']])->valid());
    }

    public function test_generate()
    {
        $constraint = new ValidateArray([
            new Length(min: 3),
            new ValidationMethod('validate'),
        ], aggregateErrors: true);

        $this->assertEquals('!\is_array($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null) ? null : (function ($value) use($data) { $valid = true; $errors = \'\'; foreach ($value as $key => $item) { if ($error = is_string($item) && (($__len_0f8134fb6038ebcd7155f1de5f067c73 = strlen($item)) < 3) ? new FieldError(\'Invalid length\') : null ?? \Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->validate($item, $data), \'Invalid value\')) { $valid = false; $errors .= \'- On item \' . $key . \': \' . (\is_array($error) ? \'\' : $error) . PHP_EOL; } } return $valid ? null : new FieldError(\'Some values are invalid :\' . PHP_EOL . $errors); })($__tmp_44e18f0f3b2a419fae74cbbaef66f40e)', $constraint->getValidator(new NullConstraintValidatorRegistry())->generate($constraint, '$data->foo ?? null', new ValidatorGenerator(new NullConstraintValidatorRegistry())));

        $customMessage = new ValidateArray(
            constraints: [
                new Length(min: 3),
                new ValidationMethod('validate'),
            ],
            message: 'My error',
            aggregateErrors: true,
        );

        $this->assertEquals('!\is_array($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null) ? null : (function ($value) use($data) { $valid = true; $errors = \'\'; foreach ($value as $key => $item) { if ($error = is_string($item) && (($__len_0f8134fb6038ebcd7155f1de5f067c73 = strlen($item)) < 3) ? new FieldError(\'Invalid length\') : null ?? \Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->validate($item, $data), \'Invalid value\')) { $valid = false; $errors .= \'- On item \' . $key . \': \' . (\is_array($error) ? \'\' : $error) . PHP_EOL; } } return $valid ? null : new FieldError(\'My error\'); })($__tmp_44e18f0f3b2a419fae74cbbaef66f40e)', $customMessage->getValidator(new NullConstraintValidatorRegistry())->generate($customMessage, '$data->foo ?? null', new ValidatorGenerator(new NullConstraintValidatorRegistry())));

        $constraint = new ValidateArray([
            new Length(min: 3),
            new ValidationMethod('validate'),
        ], aggregateErrors: false);

        $this->assertEquals('!\is_array($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null) ? null : (function ($value) use($data) { $valid = true; $errors = []; foreach ($value as $key => $item) { if ($error = is_string($item) && (($__len_0f8134fb6038ebcd7155f1de5f067c73 = strlen($item)) < 3) ? new FieldError(\'Invalid length\') : null ?? \Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->validate($item, $data), \'Invalid value\')) { $valid = false; $errors[$key] = $error; } } return $valid ? null : $errors; })($__tmp_44e18f0f3b2a419fae74cbbaef66f40e)', $constraint->getValidator(new NullConstraintValidatorRegistry())->generate($constraint, '$data->foo ?? null', new ValidatorGenerator(new NullConstraintValidatorRegistry())));

    }
}

class TestingValidateArray
{
    #[ValidateArray(constraints: [
        new Length(min: 3),
        new ValidationMethod('validate'),
    ], aggregateErrors: true)]
    public ?array $values;

    #[ValidateArray(
        constraints: [
            new Length(min: 3),
            new ValidationMethod('validate'),
        ],
        message: 'My error',
        aggregateErrors: true,
    )]
    public ?array $withoutPerItemError;

    #[ValidateArray(constraints: [
        new Length(min: 3),
        new ValidationMethod('validate'),
    ])]
    public ?array $arrayOfErrors;

    public function validate(mixed $value): bool
    {
        return is_numeric($value);
    }
}
