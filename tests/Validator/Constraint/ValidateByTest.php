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
        $this->assertEquals(['foo' => 'Invalid checksum'], $submitted->errors());

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
            return new FieldError('Invalid checksum');
        }

        return null;
    }
}
