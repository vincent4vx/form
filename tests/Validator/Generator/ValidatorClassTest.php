<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\Constraint\NullConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\ValidatorInterface;

class ValidatorClassTest extends FormTestCase
{
    public function test_empty()
    {
        $class = new ValidatorClass('TestingEmptyValidatorClass');
        $class->generate();

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class TestingEmptyValidatorClass implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        return $errors;
    }

    public function __construct(private readonly Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface $validatorRegistry)
    {
    }
}

PHP
        , $class->code()
);

        $this->assertGeneratedClass($class->code(), 'TestingEmptyValidatorClass', ValidatorInterface::class);
        $this->assertEmpty((new \TestingEmptyValidatorClass(new NullConstraintValidatorRegistry()))->validate(new \stdClass()));
    }

    public function test_addConstraintCode()
    {
        $class = new ValidatorClass('TestingWithConstraintCodeValidatorClass');
        $class->addConstraintCode('test', '($data->test ?? null) === 123 ? new FieldError("error") : null');
        $class->generate();

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class TestingWithConstraintCodeValidatorClass implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        if (!isset($previousErrors['test']) && $__error_test = (($data->test ?? null) === 123 ? new FieldError("error") : null)) {
            $errors['test'] = $__error_test;
        }

        return $errors;
    }

    public function __construct(private readonly Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface $validatorRegistry)
    {
    }
}

PHP
            , $class->code()
        );

        $this->assertGeneratedClass($class->code(), 'TestingWithConstraintCodeValidatorClass', ValidatorInterface::class);
        $this->assertEmpty((new \TestingWithConstraintCodeValidatorClass(new NullConstraintValidatorRegistry()))->validate((object) ['test' => 42]));
        $this->assertEquals(['test' => new FieldError('error')], (new \TestingWithConstraintCodeValidatorClass(new NullConstraintValidatorRegistry()))->validate((object) ['test' => 123]));
    }
}
