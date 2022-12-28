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

        $this->assertEquals(
            new FieldError(
                "Some values are invalid :\n{{ item_errors }}",
                ['item_errors' => '- On item 0: The value is too short. It should have 3 characters or more.' . PHP_EOL . '- On item 1: The value is too short. It should have 3 characters or more.' . PHP_EOL],
                ValidateArray::CODE
            ),
            $form->submit(['values' => ['12', '34']])->errors()['values']
        );

        $this->assertEquals(<<<'ERROR'
Some values are invalid :
- On item 0: The value is too short. It should have 3 characters or more.
- On item 1: The value is too short. It should have 3 characters or more.

ERROR
            , (string) $form->submit(['values' => ['12', '34']])->errors()['values']);

        $this->assertEquals(<<<'ERROR'
Some values are invalid :
- On item 1: The value is too short. It should have 3 characters or more.

ERROR
            , (string) $form->submit(['values' => ['122', '34']])->errors()['values']);

        $this->assertEquals(<<<'ERROR'
Some values are invalid :
- On item bar: The value is too short. It should have 3 characters or more.

ERROR
            , (string) $form->submit(['values' => ['foo' => '122', 'bar' => '34']])->errors()['values']);

        $this->assertEquals('My error', (string) $form->submit(['withoutPerItemError' => ['1', '2']])->errors()['withoutPerItemError']);

        $this->assertEquals([
            0 => new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE),
            1 => new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE),
        ], $form->submit(['arrayOfErrors' => ['12', '34']])->errors()['arrayOfErrors']);

        $this->assertEquals([
            1 => new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE),
        ], $form->submit(['arrayOfErrors' => ['123', '34']])->errors()['arrayOfErrors']);

        $this->assertTrue($form->submit(['arrayOfErrors' => ['123', '345']])->valid());
    }

    public function test_generate()
    {
        $constraint = new ValidateArray([
            new Length(min: 3),
            new ValidationMethod('validate'),
        ], aggregateErrors: true);

        $this->assertEquals('!\is_array($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null) ? null : (function ($value) use($data) { $valid = true; $errors = \'\'; foreach ($value as $key => $item) { if ($error = is_scalar($item) && (($__len_0f8134fb6038ebcd7155f1de5f067c73 = strlen($item)) < 3) ? new FieldError(\'The value is too short. It should have {{ min }} characters or more.\', [\'min\' => 3], \'ecdd71f6-fa22-5564-bfc7-7e836dce3378\') : null ?? \Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->validate($item, $data), \'Invalid value\', \'1b50e942-6acd-5b06-a581-d0819e7f1657\')) { $valid = false; $errors .= \'- On item \' . $key . \': \' . (\is_array($error) ? \'\' : $error) . PHP_EOL; } } return $valid ? null : new FieldError(\'Some values are invalid :\' . PHP_EOL . \'{{ item_errors }}\', [\'item_errors\' => $errors], \'1bfd08ad-82cf-57d0-a114-e9921e80986a\'); })($__tmp_44e18f0f3b2a419fae74cbbaef66f40e)', $constraint->getValidator(new NullConstraintValidatorRegistry())->generate($constraint, '$data->foo ?? null', new ValidatorGenerator(new NullConstraintValidatorRegistry())));

        $customMessage = new ValidateArray(
            constraints: [
                new Length(min: 3),
                new ValidationMethod('validate'),
            ],
            message: 'My error',
            aggregateErrors: true,
        );

        $this->assertEquals('!\is_array($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null) ? null : (function ($value) use($data) { $valid = true; $errors = \'\'; foreach ($value as $key => $item) { if ($error = is_scalar($item) && (($__len_0f8134fb6038ebcd7155f1de5f067c73 = strlen($item)) < 3) ? new FieldError(\'The value is too short. It should have {{ min }} characters or more.\', [\'min\' => 3], \'ecdd71f6-fa22-5564-bfc7-7e836dce3378\') : null ?? \Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->validate($item, $data), \'Invalid value\', \'1b50e942-6acd-5b06-a581-d0819e7f1657\')) { $valid = false; $errors .= \'- On item \' . $key . \': \' . (\is_array($error) ? \'\' : $error) . PHP_EOL; } } return $valid ? null : new FieldError(\'My error\', [\'item_errors\' => $errors], \'1bfd08ad-82cf-57d0-a114-e9921e80986a\'); })($__tmp_44e18f0f3b2a419fae74cbbaef66f40e)', $customMessage->getValidator(new NullConstraintValidatorRegistry())->generate($customMessage, '$data->foo ?? null', new ValidatorGenerator(new NullConstraintValidatorRegistry())));

        $constraint = new ValidateArray([
            new Length(min: 3),
            new ValidationMethod('validate'),
        ], aggregateErrors: false);

        $this->assertEquals('!\is_array($__tmp_44e18f0f3b2a419fae74cbbaef66f40e = $data->foo ?? null) ? null : (function ($value) use($data) { $valid = true; $errors = []; foreach ($value as $key => $item) { if ($error = is_scalar($item) && (($__len_0f8134fb6038ebcd7155f1de5f067c73 = strlen($item)) < 3) ? new FieldError(\'The value is too short. It should have {{ min }} characters or more.\', [\'min\' => 3], \'ecdd71f6-fa22-5564-bfc7-7e836dce3378\') : null ?? \Quatrevieux\Form\Validator\Constraint\ValidationMethod::toFieldError($data->validate($item, $data), \'Invalid value\', \'1b50e942-6acd-5b06-a581-d0819e7f1657\')) { $valid = false; $errors[$key] = $error; } } return $valid ? null : $errors; })($__tmp_44e18f0f3b2a419fae74cbbaef66f40e)', $constraint->getValidator(new NullConstraintValidatorRegistry())->generate($constraint, '$data->foo ?? null', new ValidatorGenerator(new NullConstraintValidatorRegistry())));

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
