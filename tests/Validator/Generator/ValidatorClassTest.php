<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
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
        return $previousErrors;
    }

    public function __construct(
        private readonly Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP
        , $class->code()
);

        $this->assertGeneratedClass($class->code(), 'TestingEmptyValidatorClass', ValidatorInterface::class);
        $this->assertEmpty((new \TestingEmptyValidatorClass(new DefaultRegistry()))->validate(new \stdClass()));
    }

    public function test_addConstraintCode()
    {
        $class = new ValidatorClass('TestingWithConstraintCodeValidatorClass');
        $class->addConstraintCode('test', FieldErrorExpression::undefined(fn (string $fieldAccessor) => $fieldAccessor . ' === 123 ? new FieldError("error") : null'));
        $class->generate();

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class TestingWithConstraintCodeValidatorClass implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        $translator = $this->registry->getTranslator();
        if (!isset($previousErrors['test']) && $__error_test = (($data->test ?? null) === 123 ? new FieldError("error") : null)) {
            $errors['test'] = is_array($__error_test) ? $__error_test : $__error_test->withTranslator($translator);
        }

        return $errors;
    }

    public function __construct(
        private readonly Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP
            , $class->code()
        );

        $this->assertGeneratedClass($class->code(), 'TestingWithConstraintCodeValidatorClass', ValidatorInterface::class);
        $this->assertEmpty((new \TestingWithConstraintCodeValidatorClass(new DefaultRegistry()))->validate((object) ['test' => 42]));
        $this->assertErrors(['test' => new FieldError('error')], (new \TestingWithConstraintCodeValidatorClass(new DefaultRegistry()))->validate((object) ['test' => 123]));
    }

    public function test_addConstraintCode_returnTypeSingle()
    {
        $class = new ValidatorClass('TestingWithConstraintCodeValidatorClassReturnTypeSingle');
        $class->addConstraintCode('test', FieldErrorExpression::single(fn (string $fieldAccessor) => $fieldAccessor . ' === 123 ? new FieldError("error") : null'));
        $class->generate();

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class TestingWithConstraintCodeValidatorClassReturnTypeSingle implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        $translator = $this->registry->getTranslator();
        if (!isset($previousErrors['test']) && $__error_test = (($data->test ?? null) === 123 ? new FieldError("error") : null)) {
            $errors['test'] = $__error_test->withTranslator($translator);
        }

        return $errors;
    }

    public function __construct(
        private readonly Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP
            , $class->code()
        );

        $this->assertGeneratedClass($class->code(), 'TestingWithConstraintCodeValidatorClassReturnTypeSingle', ValidatorInterface::class);
        $this->assertEmpty((new \TestingWithConstraintCodeValidatorClassReturnTypeSingle(new DefaultRegistry()))->validate((object) ['test' => 42]));
        $this->assertErrors(['test' => new FieldError('error')], (new \TestingWithConstraintCodeValidatorClassReturnTypeSingle(new DefaultRegistry()))->validate((object) ['test' => 123]));
    }

    public function test_addConstraintCode_returnTypeAggregate()
    {
        $class = new ValidatorClass('TestingWithConstraintCodeValidatorClassReturnTypeAggregate');
        $class->addConstraintCode('test', FieldErrorExpression::aggregate(fn (string $fieldAccessor) => $fieldAccessor . ' === 123 ? ["foo" => new FieldError("error")] : null'));
        $class->generate();

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class TestingWithConstraintCodeValidatorClassReturnTypeAggregate implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        $translator = $this->registry->getTranslator();
        if (!isset($previousErrors['test']) && $__error_test = (($data->test ?? null) === 123 ? ["foo" => new FieldError("error")] : null)) {
            $errors['test'] = $__error_test;
        }

        return $errors;
    }

    public function __construct(
        private readonly Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP
            , $class->code()
        );

        $this->assertGeneratedClass($class->code(), 'TestingWithConstraintCodeValidatorClassReturnTypeAggregate', ValidatorInterface::class);
        $this->assertEmpty((new \TestingWithConstraintCodeValidatorClassReturnTypeAggregate(new DefaultRegistry()))->validate((object) ['test' => 42]));
        $this->assertErrors(['foo' => new FieldError('error')], (new \TestingWithConstraintCodeValidatorClassReturnTypeAggregate(new DefaultRegistry()))->validate((object) ['test' => 123])['test']);
    }
}
