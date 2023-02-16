<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Ramsey\Uuid\Uuid;

class ValidateVarTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(ValidateVar::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'ValidateVar')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ValidateVarTestRequest::class) : $this->runtimeForm(ValidateVarTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['email' => 'foo@bar.fr'])->valid());
        $this->assertErrors(['email' => new FieldError('This value is not a valid.', code: ValidateVar::CODE)], $form->submit(['email' => 'invalid'])->errors());

        $this->assertTrue($form->submit(['domain' => 'foo.bar.fr'])->valid());
        $this->assertFalse($form->submit(['domain' => '.invalid'])->valid());

        $this->assertTrue($form->submit(['int' => 12])->valid());
        $this->assertFalse($form->submit(['int' => 120])->valid());
        $this->assertFalse($form->submit(['int' => 12.5])->valid());
    }

    public function test_generated_code()
    {
        $constraint = new ValidateVar(ValidateVar::INT, options: ['options' => ['min_range' => 0, 'max_range' => 100]]);

        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && filter_var(($data->foo ?? null), 257, [\'options\' => [\'min_range\' => 0, \'max_range\' => 100]]) === false ? new \Quatrevieux\Form\Validator\FieldError(\'This value is not a valid.\', [], \'2aaca916-3129-5920-a443-4968910199c4\') : null', $constraint);
    }
}

class ValidateVarTestRequest
{
    #[ValidateVar(ValidateVar::EMAIL)]
    public ?string $email;

    #[ValidateVar(ValidateVar::DOMAIN, options: FILTER_FLAG_HOSTNAME)]
    public ?string $domain;

    #[ValidateVar(ValidateVar::INT, options: ['options' => ['min_range' => 0, 'max_range' => 100]])]
    public ?float $int;
}
