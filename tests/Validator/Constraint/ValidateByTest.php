<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;

class ValidateByTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional(bool $generated): void
    {
        $this->container->set(TestingValidator::class, new TestingValidator());
        $form = $generated ? $this->generatedForm(TestingFormWithValidateBy::class) : $this->runtimeForm(TestingFormWithValidateBy::class);

        $submitted = $form->submit(['foo' => 'bar']);
        $this->assertFalse($submitted->valid());
        $this->assertErrors(['foo' => new FieldError('Invalid checksum', [], '99e51862-6756-4a02-89b0-01c81a571d3a')], $submitted->errors());

        $submitted = $form->submit(['foo' => 'ear']);
        $this->assertTrue($submitted->valid());
        $this->assertEmpty($submitted->errors());
    }
}

class TestingFormWithValidateBy
{
    #[ValidateBy(TestingValidator::class, ['checksum' => 15])]
    public string $foo;
}

class TestingValidator implements ConstraintValidatorInterface
{
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        $checksum = crc32($value) % 32;

        if ($checksum !== $constraint->options['checksum']) {
            return new FieldError('Invalid checksum', code: '99e51862-6756-4a02-89b0-01c81a571d3a');
        }

        return null;
    }
}
